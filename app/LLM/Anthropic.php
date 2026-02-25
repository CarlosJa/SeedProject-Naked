<?php

namespace App\LLM;

use Anthropic\Client;
use Anthropic\Messages\MessageParam;
use Exception;

class Anthropic
{
    private Client $client;
    private string $defaultModel = 'claude-sonnet-4-5-20250929';
    private int $defaultMaxTokens = 4096;
    private ?string $apiKey;
    private string $templatePath;

    public const MODELS = [
        'opus' => 'claude-opus-4-20250514',
        'sonnet' => 'claude-sonnet-4-5-20250929',
        'haiku' => 'claude-haiku-4-5-20251001',
    ];

    /**
     * Constructor
     */
    public function __construct(?string $apiKey = null, ?string $templatePath = null, int $timeout = 300) {
        $this->apiKey = $apiKey ?? ANTHROPIC_API_KEY;
        $this->templatePath = $templatePath ?? dirname(__DIR__, 2) . '/templates/prompts/';


        if (empty($this->apiKey)) {
            throw new Exception('Anthropic API key is required.');
        }

        if (!is_dir($this->templatePath)) {
            throw new Exception("Template directory not found: {$this->templatePath}");
        }

        if (!is_readable($this->templatePath)) {
            throw new Exception("Template directory is not readable: {$this->templatePath}");
        }

        set_time_limit($timeout);
        ini_set('default_socket_timeout', (string) $timeout);

        $this->client = new Client(apiKey: $this->apiKey);
    }


    // =========================================================================
    // CONNECTION & HEALTH
    // =========================================================================

    public function testConnection(): array
    {
        $startTime = microtime(true);

        try {
            $response = $this->client->messages->create(
                model: $this->defaultModel,
                maxTokens: 10,
                messages: [
                    MessageParam::with(role: 'user', content: 'Reply with only: OK')
                ]
            );

            $latency = round((microtime(true) - $startTime) * 1000);

            return [
                'success' => true,
                'message' => 'Connected to Anthropic API',
                'model' => $this->defaultModel,
                'response' => trim($response->content[0]->text),
                'latency_ms' => $latency,
                'usage' => [
                    'input_tokens' => $response->usage->inputTokens,
                    'output_tokens' => $response->usage->outputTokens,
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed',
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ];
        }
    }

    public function isConnected(): bool
    {
        return $this->testConnection()['success'];
    }

    public function getStatus(): array
    {
        $connection = $this->testConnection();

        return [
            'connected' => $connection['success'],
            'model' => $this->defaultModel,
            'max_tokens' => $this->defaultMaxTokens,
            'api_key_preview' => substr($this->apiKey, 0, 10) . '...' . substr($this->apiKey, -4),
            'template_path' => $this->templatePath,
            'connection_details' => $connection
        ];
    }

    // =========================================================================
    // TEMPLATE LOADING
    // =========================================================================

    public function loadTemplate(string $filename): string
    {
        $path = $this->templatePath . '/' . $filename;

        if (!file_exists($path)) {
            throw new Exception("Template not found: {$path}");
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new Exception("Failed to read template: {$path}");
        }

        return $content;
    }

    public function setTemplatePath(string $path): self
    {
        $this->templatePath = rtrim($path, '/');
        return $this;
    }

    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    // =========================================================================
    // BASIC MESSAGING
    // =========================================================================

    public function message(string $prompt, ?string $systemPrompt = null, array $options = []): string
    {
        $response = $this->messageWithMeta($prompt, $systemPrompt, $options);
        return $response['content'];
    }

    public function messageWithMeta(string $prompt, ?string $systemPrompt = null, array $options = []): array
    {
        $startTime = microtime(true);

        $messages = $this->buildMessages($prompt, $options['conversation'] ?? []);

        $params = [
            'model' => $options['model'] ?? $this->defaultModel,
            'maxTokens' => $options['max_tokens'] ?? $this->defaultMaxTokens,
            'messages' => $messages,
        ];

        if ($systemPrompt) {
            $params['system'] = $systemPrompt;
        }

        if (isset($options['temperature'])) {
            $params['temperature'] = $options['temperature'];
        }

        if (isset($options['stop_sequences'])) {
            $params['stopSequences'] = $options['stop_sequences'];
        }

        $response = $this->client->messages->create(...$params);

        return [
            'content' => $response->content[0]->text,
            'model' => $response->model,
            'stop_reason' => $response->stopReason,
            'latency_ms' => round((microtime(true) - $startTime) * 1000),
            'usage' => [
                'input_tokens' => $response->usage->inputTokens,
                'output_tokens' => $response->usage->outputTokens,
                'total_tokens' => $response->usage->inputTokens + $response->usage->outputTokens,
            ]
        ];
    }

    public function conversation(array $messages, ?string $systemPrompt = null, array $options = []): array
    {
        $messageParams = [];
        foreach ($messages as $msg) {
            $messageParams[] = MessageParam::with(role: $msg['role'], content: $msg['content']);
        }

        $params = [
            'model' => $options['model'] ?? $this->defaultModel,
            'maxTokens' => $options['max_tokens'] ?? $this->defaultMaxTokens,
            'messages' => $messageParams,
        ];

        if ($systemPrompt) {
            $params['system'] = $systemPrompt;
        }

        $response = $this->client->messages->create(...$params);

        return [
            'content' => $response->content[0]->text,
            'usage' => [
                'input_tokens' => $response->usage->inputTokens,
                'output_tokens' => $response->usage->outputTokens,
            ]
        ];
    }


    // =========================================================================
    // CACHED MESSAGING
    // =========================================================================

    /**
     * Message with prompt caching - template is cached, variables are not
     */
    public function messageWithCache(
        string $prompt,
        string $cachedContext,
        ?string $dynamicContext = null,
        array $options = []
    ): array {
        $startTime = microtime(true);

        $systemBlocks = [
            [
                'type' => 'text',
                'text' => $cachedContext,
                'cache_control' => ['type' => 'ephemeral']
            ]
        ];

        if ($dynamicContext) {
            $systemBlocks[] = [
                'type' => 'text',
                'text' => $dynamicContext
            ];
        }

        $response = $this->client->messages->create(
            model: $options['model'] ?? $this->defaultModel,
            maxTokens: $options['max_tokens'] ?? $this->defaultMaxTokens,
            system: $systemBlocks,
            messages: [
                MessageParam::with(role: 'user', content: $prompt)
            ]
        );

        $cacheCreation = $response->usage->cacheCreationInputTokens ?? 0;
        $cacheRead = $response->usage->cacheReadInputTokens ?? 0;

        return [
            'content' => $response->content[0]->text,
            'model' => $response->model,
            'latency_ms' => round((microtime(true) - $startTime) * 1000),
            'usage' => [
                'input_tokens' => $response->usage->inputTokens,
                'output_tokens' => $response->usage->outputTokens,
                'cache_creation_tokens' => $cacheCreation,
                'cache_read_tokens' => $cacheRead,
            ],
            'cache_hit' => $cacheRead > 0,
            'cache_status' => $this->getCacheStatus($cacheCreation, $cacheRead)
        ];
    }

    private function getCacheStatus(int $created, int $read): string
    {
        if ($read > 0) return 'hit';
        if ($created > 0) return 'created';
        return 'none';
    }

    // =========================================================================
    // ARTICLE GENERATION
    // =========================================================================

    /**
     * Build the dynamic variables block (topic, word count, etc.)
     * This is NOT cached - changes per article
     */
    public function buildArticleVariables(array $config): string
    {
        $lines = [];

        if (!empty($config['topic'])) {
            $lines[] = "TOPIC: {$config['topic']}";
        }
        if (!empty($config['domain'])) {
            $lines[] = "DOMAIN: {$config['domain']}";
        }
        if (!empty($config['industry'])) {
            $lines[] = "INDUSTRY: {$config['industry']}";
        }
        if (!empty($config['target_audience'])) {
            $lines[] = "AUDIENCE: {$config['target_audience']}";
        }
        if (!empty($config['knowledge_level'])) {
            $lines[] = "KNOWLEDGE_LEVEL: {$config['knowledge_level']}";
        }
        if (!empty($config['tone'])) {
            $lines[] = "TONE: {$config['tone']}";
        }
        if (!empty($config['word_count'])) {
            $lines[] = "WORD_COUNT: {$config['word_count']}";
        }
        if (!empty($config['location'])) {
            $lines[] = "LOCATION: {$config['location']}";
        }

        $lines[] = "CURRENT_DATE: " . date('Y-m-d');

        return implode("\n", $lines);
    }

    /**
     * Generate article using template + variables (legacy single-file method)
     * Template (with business profile) is cached
     * Variables (topic, word count, etc.) are dynamic
     */
    public function generateArticle(string $templateFile, array $config): array
    {
        // Load template - this includes the master prompt + business profile
        // This part gets cached
        $template = $this->loadTemplate($templateFile);

        // Build dynamic variables - this changes per article
        $variables = $this->buildArticleVariables($config);

        return $this->messageWithCache(
            prompt: $config['prompt'] ?? 'BEGIN GENERATION NOW.',
            cachedContext: $template,
            dynamicContext: $variables,
            options: [
                'max_tokens' => $config['max_tokens'] ?? 8192,
                'model' => $config['model'] ?? $this->defaultModel
            ]
        );
    }

    /**
     * Generate article using 3-block structure with prompt caching
     *
     * Block 1: Business Profile (cached - client specific)
     * Block 2: Article Instructions (cached - shared across all clients)
     * Block 3: Config Variables (dynamic - not cached)
     *
     * Cache behavior:
     * - Blocks 1 & 2 are cached together as a prefix
     * - When processing multiple articles for same client, cache hits on blocks 1+2
     * - Block 3 varies per article, never cached
     *
     * @param string $businessProfile Client-specific business profile content
     * @param string $articleInstructions Shared writing instructions content
     * @param array $config Article configuration (topic, word_count, etc.)
     *                      - 'enable_web_search' => bool (default: false)
     *                      - 'reference_urls' => array of URLs to research
     * @return array Response with content, usage, and cache status
     */
// =============================================================================
// UPDATED Anthropic::generateArticleWithBlocks()
// =============================================================================
    public function generateArticleWithBlocks(
        string $businessProfile,
        string $articleInstructions,
        array $projectConfig,
        string $referenceContent = '',
        bool $useWebSearch = false
    ): array {
        $startTime = microtime(true);

        // Build article variables (Block 3)
        $articleVariables = $this->buildArticleVariables($projectConfig);

        // DEEPCRAWL MODE: Inject pre-fetched content into Block 3
        if (!empty($referenceContent)) {
            $articleVariables .= "\n\n" . $referenceContent;
        }

        // WEBSEARCH MODE: Add URL hints for Claude to search
        if ($useWebSearch && !empty($projectConfig['reference_urls'])) {
            $urlHints = "\n\n<search_guidance>\n";
            $urlHints .= "Consider searching these sources for authoritative information:\n";
            foreach ($projectConfig['reference_urls'] as $ref) {
                $url = is_array($ref) ? $ref['url'] : $ref;
                $notes = is_array($ref) ? ($ref['notes'] ?? '') : '';
                $urlHints .= "- {$url}";
                if ($notes) $urlHints .= " ({$notes})";
                $urlHints .= "\n";
            }
            $urlHints .= "</search_guidance>";
            $articleVariables .= $urlHints;
        }

        // =========================================================================
        // BUILD THE FULL PROMPT (for logging)
        // =========================================================================
        $block1 = "<business_profile>\n{$businessProfile}\n</business_profile>";
        $block2 = "<article_instructions>\n{$articleInstructions}\n</article_instructions>";
        $block3 = "<article_request>\n{$articleVariables}\n</article_request>";

        $fullPrompt = $block1 . "\n\n" . $block2 . "\n\n" . $block3;

        // Build messages
        $messages = [
            [
                'role' => 'user',
                'content' => [
                    // Block 1: Business Profile (cached)
                    [
                        'type' => 'text',
                        'text' => $block1,
                        'cache_control' => ['type' => 'ephemeral']
                    ],
                    // Block 2: Article Instructions (cached)
                    [
                        'type' => 'text',
                        'text' => $block2,
                        'cache_control' => ['type' => 'ephemeral']
                    ],
                    // Block 3: Article Variables + References (dynamic)
                    [
                        'type' => 'text',
                        'text' => $block3
                    ]
                ]
            ]
        ];

        // Call API
        if ($useWebSearch) {
            $response = $this->client->messages->create(
                model: 'claude-sonnet-4-5-20250929',
                maxTokens: 8192,
                messages: $messages,
                tools: [
                    [
                        'type' => 'web_search_20250305',
                        'name' => 'web_search',
                    ]
                ]
            );
        } else {
            $response = $this->client->messages->create(
                model: 'claude-sonnet-4-5-20250929',
                maxTokens: 8192,
                messages: $messages
            );
        }

        // Parse response
        $cacheCreation = $response->usage->cacheCreationInputTokens ?? 0;
        $cacheRead = $response->usage->cacheReadInputTokens ?? 0;

        return [
            'content' => $response->content[0]->text,
            'model' => $response->model,
            'stop_reason' => $response->stopReason,
            'latency_ms' => round((microtime(true) - $startTime) * 1000),
            'usage' => [
                'input_tokens' => $response->usage->inputTokens,
                'output_tokens' => $response->usage->outputTokens,
                'cache_creation_tokens' => $cacheCreation,
                'cache_read_tokens' => $cacheRead,
            ],
            'cache_status' => $this->getCacheStatus($cacheCreation, $cacheRead),
            'prompt_sent' => $fullPrompt,  // NEW: Full prompt for logging
        ];
    }


    /**
     * Build instruction for reference URLs
     */
    private function buildReferenceUrlsInstruction(array $urls): string
    {
        $instruction = "REFERENCE URLS FOR RESEARCH:\n";
        $instruction .= "Search and reference the following URLs for accurate, current information:\n\n";

        foreach ($urls as $index => $url) {
            $num = $index + 1;
            if (is_array($url)) {
                // URL with description: ['url' => '...', 'description' => '...']
                $instruction .= "{$num}. {$url['url']}\n";
                if (!empty($url['description'])) {
                    $instruction .= "   Purpose: {$url['description']}\n";
                }
            } else {
                // Simple URL string
                $instruction .= "{$num}. {$url}\n";
            }
        }

        $instruction .= "\nUse web search to fetch current information from these sources. ";
        $instruction .= "Cite specific facts, statistics, or requirements found on these pages. ";
        $instruction .= "You may also search for additional supporting information as needed.";

        return $instruction;
    }

    /**
     * Extract text content from response blocks (handles mixed content with tool use)
     */
    private function extractTextContent(array $contentBlocks): string
    {
        $textParts = [];

        foreach ($contentBlocks as $block) {
            if (isset($block->type) && $block->type === 'text') {
                $textParts[] = $block->text;
            }
        }

        return implode("\n", $textParts);
    }

    /**
     * Generate article using multi-turn conversation style
     *
     * This approach chunks the prompt into conversational turns:
     * - Turn 1: User sends business profile
     * - Turn 2: Assistant acknowledges persona
     * - Turn 3: User sends article instructions
     * - Turn 4: Assistant confirms understanding
     * - Turn 5: User sends config and triggers generation
     *
     * Benefits: May improve instruction following for complex prompts
     * Tradeoff: Extra tokens for assistant acknowledgments
     *
     * @param string $businessProfile Client-specific business profile content
     * @param string $articleInstructions Shared writing instructions content
     * @param array $config Article configuration (topic, word_count, etc.)
     * @return array Response with content, usage, and cache status
     */
    public function generateArticleMultiTurn(
        string $businessProfile,
        string $articleInstructions,
        array $config
    ): array {
        $startTime = microtime(true);

        // Build dynamic config variables
        $configVariables = $this->buildArticleVariables($config);

        $response = $this->client->messages->create(
            model: $config['model'] ?? $this->defaultModel,
            maxTokens: $config['max_tokens'] ?? 8192,
            messages: [
                // Turn 1: Business Profile
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "Here is the business profile you will write as:\n\n" . $businessProfile,
                            'cache_control' => ['type' => 'ephemeral'],
                        ],
                    ],
                ],
                // Turn 2: Assistant acknowledges persona
                [
                    'role' => 'assistant',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "I understand. I am now embodying this business identity and will write from their perspective, using their voice, expertise, and experience as defined in the profile.",
                            'cache_control' => ['type' => 'ephemeral'],
                        ],
                    ],
                ],
                // Turn 3: Article Instructions
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "Here are the article writing instructions:\n\n" . $articleInstructions,
                            'cache_control' => ['type' => 'ephemeral'],
                        ],
                    ],
                ],
                // Turn 4: Assistant confirms understanding
                [
                    'role' => 'assistant',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => "Ready. I will follow these writing rules: early answer with recommendation, 'This breaks down when...' statement, field notes from practitioner experience, no blacklisted phrases, boundary conditions section, and output valid JSON only.",
                            'cache_control' => ['type' => 'ephemeral'],
                        ],
                    ],
                ],
                // Turn 5: Config Variables + Generate
                [
                    'role' => 'user',
                    'content' => $configVariables . "\n\nBEGIN GENERATION.",
                ],
            ],
        );

        $cacheCreation = $response->usage->cacheCreationInputTokens ?? 0;
        $cacheRead = $response->usage->cacheReadInputTokens ?? 0;

        return [
            'content' => $response->content[0]->text,
            'model' => $response->model,
            'stop_reason' => $response->stopReason,
            'latency_ms' => round((microtime(true) - $startTime) * 1000),
            'usage' => [
                'input_tokens' => $response->usage->inputTokens,
                'output_tokens' => $response->usage->outputTokens,
                'cache_creation_tokens' => $cacheCreation,
                'cache_read_tokens' => $cacheRead,
            ],
            'cache_hit' => $cacheRead > 0,
            'cache_status' => $this->getCacheStatus($cacheCreation, $cacheRead),
        ];
    }

    /**
     * Generate multiple articles (same template = cache hits after first)
     */
    public function generateArticleBatch(string $templateFile, array $articles): array
    {
        $results = [];

        foreach ($articles as $index => $config) {
            $results[] = [
                'index' => $index,
                'topic' => $config['topic'] ?? 'Unknown',
                'result' => $this->generateArticle($templateFile, $config)
            ];
        }

        return $results;
    }

    // =========================================================================
    // UTILITY METHODS
    // =========================================================================

    private function buildMessages(string $prompt, array $conversation = []): array
    {
        $messages = [];

        foreach ($conversation as $msg) {
            $messages[] = MessageParam::with(role: $msg['role'], content: $msg['content']);
        }

        $messages[] = MessageParam::with(role: 'user', content: $prompt);

        return $messages;
    }

    public function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }

    /**
     * Check if content meets minimum cache threshold (1024 tokens for Sonnet)
     */
    public function meetsCacheMinimum(string $text): bool
    {
        return $this->estimateTokens($text) >= 1024;
    }

    public function estimateCost(int $inputTokens, int $outputTokens, ?string $model = null): array
    {
        $model = $model ?? $this->defaultModel;

        $pricing = [
            'claude-opus-4-20250514' => ['input' => 15.00, 'output' => 75.00],
            'claude-sonnet-4-5-20250929' => ['input' => 3.00, 'output' => 15.00],
            'claude-haiku-4-5-20251001' => ['input' => 0.80, 'output' => 4.00],
        ];

        $rates = $pricing[$model] ?? $pricing['claude-sonnet-4-5-20250929'];

        $inputCost = ($inputTokens / 1_000_000) * $rates['input'];
        $outputCost = ($outputTokens / 1_000_000) * $rates['output'];

        return [
            'input_cost' => round($inputCost, 6),
            'output_cost' => round($outputCost, 6),
            'total_cost' => round($inputCost + $outputCost, 6),
            'model' => $model
        ];
    }

    public function formatCost(array $usage): string {
        $cost = $this->estimateCostWithCache($usage);

        $output = "Cost Breakdown:\n";
        $output .= "  Input:  $" . number_format($cost['input_cost'], 4) . "\n";
        $output .= "  Output: $" . number_format($cost['output_cost'], 4) . "\n";

        if ($cost['cache_write_cost'] > 0) {
            $output .= "  Cache Write: $" . number_format($cost['cache_write_cost'], 4) . "\n";
        }
        if ($cost['cache_read_cost'] > 0) {
            $output .= "  Cache Read:  $" . number_format($cost['cache_read_cost'], 4) . "\n";
        }

        $output .= "  ─────────────\n";
        $output .= "  Total:  $" . number_format($cost['total_cost'], 4) . "\n";

        if ($cost['savings'] > 0) {
            $output .= "  Saved:  $" . number_format($cost['savings'], 4) . " ({$cost['savings_percent']}%)\n";
        }

        return $output;
    }

    public function estimateCostWithCache(array $usage, ?string $model = null): array
    {
        $model = $model ?? $this->defaultModel;

        $pricing = [
            'claude-opus-4-20250514' => ['input' => 15.00, 'output' => 75.00, 'cache_write' => 18.75, 'cache_read' => 1.50],
            'claude-sonnet-4-5-20250929' => ['input' => 3.00, 'output' => 15.00, 'cache_write' => 3.75, 'cache_read' => 0.30],
            'claude-haiku-4-5-20251001' => ['input' => 0.80, 'output' => 4.00, 'cache_write' => 1.00, 'cache_read' => 0.08],
        ];

        $rates = $pricing[$model] ?? $pricing['claude-sonnet-4-5-20250929'];

        $inputCost = (($usage['input_tokens'] ?? 0) / 1_000_000) * $rates['input'];
        $outputCost = (($usage['output_tokens'] ?? 0) / 1_000_000) * $rates['output'];
        $cacheWriteCost = (($usage['cache_creation_tokens'] ?? 0) / 1_000_000) * $rates['cache_write'];
        $cacheReadCost = (($usage['cache_read_tokens'] ?? 0) / 1_000_000) * $rates['cache_read'];

        $totalCost = $inputCost + $outputCost + $cacheWriteCost + $cacheReadCost;

        $cacheTokens = ($usage['cache_creation_tokens'] ?? 0) + ($usage['cache_read_tokens'] ?? 0);
        $costWithoutCache = (($usage['input_tokens'] + $cacheTokens) / 1_000_000) * $rates['input'] + $outputCost;
        $savings = $costWithoutCache - $totalCost;

        return [
            'input_cost' => round($inputCost, 6),
            'output_cost' => round($outputCost, 6),
            'cache_write_cost' => round($cacheWriteCost, 6),
            'cache_read_cost' => round($cacheReadCost, 6),
            'total_cost' => round($totalCost, 6),
            'cost_without_cache' => round($costWithoutCache, 6),
            'savings' => round($savings, 6),
            'savings_percent' => $costWithoutCache > 0 ? round(($savings / $costWithoutCache) * 100, 2) : 0,
            'model' => $model
        ];
    }

    // =========================================================================
    // CONFIGURATION
    // =========================================================================

    public function setModel(string $model): self
    {
        $this->defaultModel = $model;
        return $this;
    }

    public function useModel(string $preset): self
    {
        if (isset(self::MODELS[$preset])) {
            $this->defaultModel = self::MODELS[$preset];
        }
        return $this;
    }

    public function setMaxTokens(int $tokens): self
    {
        $this->defaultMaxTokens = $tokens;
        return $this;
    }

    public function getModel(): string
    {
        return $this->defaultModel;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getAvailableModels(): array
    {
        return self::MODELS;
    }
}
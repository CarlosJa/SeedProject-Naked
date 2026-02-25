<?php

namespace App\LLM;

use Exception;
use Gemini as GeminiClient;
use Gemini\Data\Content;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Tool;
use Gemini\Data\GoogleSearch;
use Gemini\Enums\ResponseMimeType;

class GeminiService {
    private $client;
    private string $defaultModel = 'gemini-3-pro-preview';
    private int $defaultMaxTokens = 8192;
    private ?string $apiKey;
    private string $templatePath;

    // Model presets for different use cases
    // - pro: Best for humanizing text, creative nuance, SEO strategy, and fact-checking
    // - flash: Best for bulk processing, initial drafts, AEO structure checks
    // - flash-lite: Cost saver for simple tasks like meta-tag generation or keyword extraction
    public const MODELS = [
        'pro' => 'gemini-3-pro-preview',           // Best for humanizing, SEO strategy
        'flash' => 'gemini-3-flash-preview',       // Faster, good for SEO review pass
        'flash-lite' => 'gemini-2.5-flash-lite',   // Cost saver for simple tasks
        'flash-2.5' => 'gemini-2.5-flash',         // Legacy flash model
    ];

    // Human-readable labels for UI dropdowns
    public const MODEL_LABELS = [
        'gemini-3-pro-preview' => 'Gemini 3 Pro (Best for Humanizing)',
        'gemini-3-flash-preview' => 'Gemini 3 Flash (Fast & Smart)',
        'gemini-2.5-flash' => 'Gemini 2.5 Flash (Legacy)',
        'gemini-2.5-flash-lite' => 'Gemini 2.5 Flash Lite (Budget)',
    ];

    /**
     * Constructor
     */
    public function __construct(?string $apiKey = null, ?string $templatePath = null, int $timeout = 300)
    {
        $this->apiKey = $apiKey ?? (defined('GEMINI_API_KEY') ? GEMINI_API_KEY : null);

        if (empty($this->apiKey)) {
            throw new Exception('Gemini API key is required.');
        }

        $this->templatePath = $templatePath ?? '/www/wwwroot/appCarlos/templates/prompts';

        if (!is_dir($this->templatePath)) {
            throw new Exception("Template directory not found: {$this->templatePath}");
        }

        if (!is_readable($this->templatePath)) {
            throw new Exception("Template directory is not readable: {$this->templatePath}");
        }

        set_time_limit($timeout);
        ini_set('default_socket_timeout', (string) $timeout);

        $this->client = GeminiClient::client($this->apiKey);
    }

    // =========================================================================
    // CONNECTION & HEALTH
    // =========================================================================

    public function testConnection(): array
    {
        $startTime = microtime(true);

        try {
            $response = $this->client
                ->generativeModel(model: $this->defaultModel)
                ->generateContent('Reply with only: OK');

            $latency = round((microtime(true) - $startTime) * 1000);

            // Extract usage from response
            $usage = $this->extractUsage($response);

            return [
                'success' => true,
                'message' => 'Connected to Gemini API',
                'model' => $this->defaultModel,
                'response' => trim($response->text()),
                'latency_ms' => $latency,
                'usage' => $usage,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed',
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
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
            'connection_details' => $connection,
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

    /**
     * GEMINI SERVICE UPDATE
     *
     * Replace the existing messageWithMeta() method with this updated version
     * that supports JSON response mode via 'response_mime_type' option.
     *
     * Location: App\Components\GeminiService
     * Method: messageWithMeta()
     */

    public function messageWithMeta(string $prompt, ?string $systemPrompt = null, array $options = []): array
    {
        $startTime = microtime(true);

        $model = $this->client->generativeModel(
            model: $options['model'] ?? $this->defaultModel
        );

        // Add system instruction if provided
        if ($systemPrompt) {
            $model = $model->withSystemInstruction(Content::parse($systemPrompt));
        }

        // Build generation config
        $configParams = [
            'maxOutputTokens' => $options['max_tokens'] ?? $this->defaultMaxTokens,
        ];

        if (isset($options['response_mime_type']) && $options['response_mime_type'] === 'application/json') {
            $configParams['responseMimeType'] = ResponseMimeType::APPLICATION_JSON;
        }

        if (isset($options['temperature'])) {
            $configParams['temperature'] = $options['temperature'];
        }

        if (isset($options['stop_sequences'])) {
            $configParams['stopSequences'] = $options['stop_sequences'];
        }

        // Support JSON response mode
        if (isset($options['response_mime_type']) && $options['response_mime_type'] === 'application/json') {
            $configParams['responseMimeType'] = ResponseMimeType::APPLICATION_JSON;
        }

        $model = $model->withGenerationConfig(new GenerationConfig(...$configParams));

        // Enable Google Search if requested
        if ($options['use_search'] ?? false) {
            $model = $model->withTool(new Tool(googleSearch: new GoogleSearch()));
        }

        $response = $model->generateContent($prompt);

        $usage = $this->extractUsage($response);

        return [
            'content' => $response->text(),
            'model' => $options['model'] ?? $this->defaultModel,
            'stop_reason' => $response->candidates[0]->finishReason ?? null,
            'latency_ms' => round((microtime(true) - $startTime) * 1000),
            'usage' => $usage,
        ];
    }


    // =========================================================================
    // ARTICLE GENERATION
    // =========================================================================

    /**
     * Build the dynamic variables block (topic, word count, etc.)
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

        return implode("\n", $lines);
    }

    /**
     * Generate article using template + variables
     * Uses Google Search for fact verification
     */
    public function generateArticle(string $templateFile, array $config): array
    {
        $startTime = microtime(true);

        // Load template - this includes the master prompt + business profile
        $template = $this->loadTemplate($templateFile);

        // Build dynamic variables
        $variables = $this->buildArticleVariables($config);

        // Combine template with variables
        $systemPrompt = $template . "\n\n" . $variables;

        // Build the model with configuration
        $model = $this->client->generativeModel(
            model: $config['model'] ?? $this->defaultModel
        );

        // Add system instruction (template + variables)
        $model = $model->withSystemInstruction(Content::parse($systemPrompt));

        // Add generation config
        $model = $model->withGenerationConfig(new GenerationConfig(
            maxOutputTokens: $config['max_tokens'] ?? $this->defaultMaxTokens,
        ));

        // Enable Google Search for fact verification
        if ($config['use_search'] ?? true) {
            $model = $model->withTool(new Tool(googleSearch: new GoogleSearch()));
        }

        // Generate content
        $userPrompt = $config['prompt'] ?? 'BEGIN GENERATION NOW.';
        $response = $model->generateContent($userPrompt);

        $usage = $this->extractUsage($response);
        $latency = round((microtime(true) - $startTime) * 1000);

        // Check if grounding was used
        $groundingUsed = $this->checkGroundingUsed($response);

        return [
            'content' => $response->text(),
            'model' => $config['model'] ?? $this->defaultModel,
            'latency_ms' => $latency,
            'grounding_used' => $groundingUsed,
            'usage' => $usage,
        ];
    }

    /**
     * Generate article with structured JSON output
     */
    public function generateArticleStructured(string $templateFile, array $config): array
    {
        $startTime = microtime(true);

        // Load template
        $template = $this->loadTemplate($templateFile);

        // Build dynamic variables
        $variables = $this->buildArticleVariables($config);

        // Define JSON structure for article
        $jsonSchema = $config['json_schema'] ?? [
            'title' => 'Article title',
            'meta_description' => 'SEO meta description',
            'content' => 'Full article content in HTML format',
            'sections' => [
                ['heading' => 'Section heading', 'content' => 'Section content']
            ],
            'tags' => ['relevant', 'tags'],
            'sources' => [
                ['title' => 'Source title', 'url' => 'Source URL']
            ],
        ];

        $schemaJson = json_encode($jsonSchema, JSON_PRETTY_PRINT);

        // Combine template with variables and JSON instruction
        $systemPrompt = $template . "\n\n" . $variables . "\n\n" .
            "IMPORTANT: Respond ONLY with valid JSON using this structure:\n" . $schemaJson;

        // Build the model
        $model = $this->client->generativeModel(
            model: $config['model'] ?? $this->defaultModel
        );

        $model = $model->withSystemInstruction(Content::parse($systemPrompt));

        // Enable JSON output
        $model = $model->withGenerationConfig(new GenerationConfig(
            maxOutputTokens: $config['max_tokens'] ?? $this->defaultMaxTokens,
            responseMimeType: ResponseMimeType::APPLICATION_JSON,
        ));

        // Enable Google Search
        if ($config['use_search'] ?? true) {
            $model = $model->withTool(new Tool(googleSearch: new GoogleSearch()));
        }

        $userPrompt = $config['prompt'] ?? 'BEGIN GENERATION NOW.';
        $response = $model->generateContent($userPrompt);

        $usage = $this->extractUsage($response);
        $latency = round((microtime(true) - $startTime) * 1000);
        $groundingUsed = $this->checkGroundingUsed($response);

        // Parse JSON response
        $articleData = null;
        try {
            $articleData = $response->json();
        } catch (Exception $e) {
            // Fall back to text if JSON parsing fails
            $articleData = ['content' => $response->text(), 'parse_error' => $e->getMessage()];
        }

        return [
            'content' => $response->text(),
            'article' => $articleData,
            'model' => $config['model'] ?? $this->defaultModel,
            'latency_ms' => $latency,
            'grounding_used' => $groundingUsed,
            'usage' => $usage,
        ];
    }

    /**
     * Generate multiple articles
     */
    public function generateArticleBatch(string $templateFile, array $articles): array
    {
        $results = [];

        foreach ($articles as $index => $config) {
            $results[] = [
                'index' => $index,
                'topic' => $config['topic'] ?? 'Unknown',
                'result' => $this->generateArticle($templateFile, $config),
            ];
        }

        return $results;
    }

    // =========================================================================
    // ARTICLE REVIEW
    // =========================================================================

    /**
     * Review an article for quality and factual accuracy
     */
    public function reviewArticle(string $articleContent, array $options = []): array
    {
        $startTime = microtime(true);

        $criteria = $options['criteria'] ?? [
            'grammar_spelling',
            'factual_accuracy',
            'readability',
            'seo_optimization',
            'engagement',
            'structure',
            'tone_consistency',
        ];

        $criteriaList = implode(', ', $criteria);

        $systemPrompt = <<<PROMPT
You are an expert editor and content quality analyst. Your task is to thoroughly review articles
and provide detailed, actionable feedback. Use Google Search to verify any factual claims made
in the article. Be thorough but constructive.
PROMPT;

        $jsonSchema = [
            'overall_score' => 85,
            'summary' => 'Brief overall assessment',
            'criteria_scores' => [
                'example_criterion' => [
                    'score' => 90,
                    'feedback' => 'Detailed feedback',
                ],
            ],
            'strengths' => ['List of article strengths'],
            'improvements' => [
                [
                    'priority' => 'high|medium|low',
                    'issue' => 'Issue description',
                    'suggestion' => 'How to fix it',
                ],
            ],
            'fact_check' => [
                [
                    'claim' => 'Claim from the article',
                    'verified' => true,
                    'source' => 'Source or note',
                ],
            ],
        ];

        $schemaJson = json_encode($jsonSchema, JSON_PRETTY_PRINT);

        $userPrompt = <<<PROMPT
Review the following article and provide a comprehensive quality assessment.

ARTICLE TO REVIEW:
{$articleContent}

Evaluate on these criteria: {$criteriaList}

Use Google Search to verify factual claims.

Respond with JSON in this structure:
{$schemaJson}
PROMPT;

        $model = $this->client->generativeModel(
            model: $options['model'] ?? $this->defaultModel
        );

        $model = $model->withSystemInstruction(Content::parse($systemPrompt));

        $model = $model->withGenerationConfig(new GenerationConfig(
            maxOutputTokens: $options['max_tokens'] ?? $this->defaultMaxTokens,
            responseMimeType: ResponseMimeType::APPLICATION_JSON,
        ));

        // Enable Google Search for fact-checking
        $model = $model->withTool(new Tool(googleSearch: new GoogleSearch()));

        $response = $model->generateContent($userPrompt);

        $usage = $this->extractUsage($response);
        $latency = round((microtime(true) - $startTime) * 1000);

        $reviewData = null;
        try {
            $reviewData = $response->json();
        } catch (Exception $e) {
            $reviewData = ['content' => $response->text(), 'parse_error' => $e->getMessage()];
        }

        return [
            'content' => $response->text(),
            'review' => $reviewData,
            'model' => $options['model'] ?? $this->defaultModel,
            'latency_ms' => $latency,
            'usage' => $usage,
        ];
    }

    // =========================================================================
    // RESEARCH
    // =========================================================================

    /**
     * Research a topic using Google Search
     */
    public function research(string $query, array $options = []): array
    {
        $startTime = microtime(true);

        $depth = $options['depth'] ?? 2;

        $depthInstructions = match ($depth) {
            1 => 'Provide a quick overview with 3-5 key points.',
            2 => 'Provide a comprehensive overview with detailed findings.',
            3 => 'Provide an exhaustive analysis covering all aspects.',
            default => 'Provide a comprehensive overview.',
        };

        $systemPrompt = <<<PROMPT
You are a research assistant. Research topics thoroughly using Google Search.
Verify information across multiple sources. Be accurate and cite sources.
PROMPT;

        $jsonSchema = [
            'topic' => 'The research topic',
            'summary' => 'Executive summary',
            'key_findings' => [
                ['finding' => 'Key finding', 'confidence' => 'high|medium|low'],
            ],
            'statistics' => [
                ['stat' => 'Statistic', 'source' => 'Source'],
            ],
            'sources' => [
                ['title' => 'Source title', 'url' => 'URL', 'credibility' => 'high|medium|low'],
            ],
        ];

        $schemaJson = json_encode($jsonSchema, JSON_PRETTY_PRINT);

        $userPrompt = <<<PROMPT
Research: {$query}

{$depthInstructions}

Use Google Search to find current and accurate information.

Respond with JSON:
{$schemaJson}
PROMPT;

        $model = $this->client->generativeModel(
            model: $options['model'] ?? $this->defaultModel
        );

        $model = $model->withSystemInstruction(Content::parse($systemPrompt));

        $model = $model->withGenerationConfig(new GenerationConfig(
            maxOutputTokens: $options['max_tokens'] ?? $this->defaultMaxTokens,
            responseMimeType: ResponseMimeType::APPLICATION_JSON,
        ));

        $model = $model->withTool(new Tool(googleSearch: new GoogleSearch()));

        $response = $model->generateContent($userPrompt);

        $usage = $this->extractUsage($response);
        $latency = round((microtime(true) - $startTime) * 1000);

        $researchData = null;
        try {
            $researchData = $response->json();
        } catch (Exception $e) {
            $researchData = ['content' => $response->text(), 'parse_error' => $e->getMessage()];
        }

        return [
            'content' => $response->text(),
            'research' => $researchData,
            'model' => $options['model'] ?? $this->defaultModel,
            'latency_ms' => $latency,
            'usage' => $usage,
        ];
    }

    // =========================================================================
    // UTILITY METHODS
    // =========================================================================

    /**
     * Extract usage information from response
     */
    private function extractUsage($response): array
    {
        $usage = [
            'input_tokens' => 0,
            'output_tokens' => 0,
            'total_tokens' => 0,
        ];

        // Try to get usage metadata from response
        if (isset($response->usageMetadata)) {
            $usage['input_tokens'] = $response->usageMetadata->promptTokenCount ?? 0;
            $usage['output_tokens'] = $response->usageMetadata->candidatesTokenCount ?? 0;
            $usage['total_tokens'] = $response->usageMetadata->totalTokenCount ??
                ($usage['input_tokens'] + $usage['output_tokens']);
        }

        return $usage;
    }

    /**
     * Check if grounding/search was used in the response
     */
    private function checkGroundingUsed($response): bool
    {
        // Check for grounding metadata in response
        if (isset($response->candidates[0]->groundingMetadata)) {
            return true;
        }

        return false;
    }

    public function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }

    public function estimateCost(array $usage, ?string $model = null): array
    {
        $model = $model ?? $this->defaultModel;

        // Gemini pricing per 1M tokens (as of January 2026)
        $pricing = [
            'gemini-3-pro-preview' => ['input' => 2.00, 'output' => 12.00],
            'gemini-3-flash-preview' => ['input' => 0.20, 'output' => 0.80],
            'gemini-2.5-flash' => ['input' => 0.15, 'output' => 0.60],
            'gemini-2.5-flash-lite' => ['input' => 0.075, 'output' => 0.30],
        ];

        $rates = $pricing[$model] ?? $pricing['gemini-3-pro-preview'];

        $inputCost = (($usage['input_tokens'] ?? 0) / 1_000_000) * $rates['input'];
        $outputCost = (($usage['output_tokens'] ?? 0) / 1_000_000) * $rates['output'];

        return [
            'input_cost' => round($inputCost, 6),
            'output_cost' => round($outputCost, 6),
            'total_cost' => round($inputCost + $outputCost, 6),
            'model' => $model,
        ];
    }

    public function formatCost(array $usage): string
    {
        $cost = $this->estimateCost($usage);

        $output = "Cost Breakdown:\n";
        $output .= "  Input:  $" . number_format($cost['input_cost'], 4) . "\n";
        $output .= "  Output: $" . number_format($cost['output_cost'], 4) . "\n";
        $output .= "  ─────────────\n";
        $output .= "  Total:  $" . number_format($cost['total_cost'], 4) . "\n";

        return $output;
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

    public function getClient()
    {
        return $this->client;
    }

    public function getAvailableModels(): array
    {
        return self::MODELS;
    }

    /**
     * Get model labels for UI dropdowns
     */
    public static function getModelLabels(): array
    {
        return self::MODEL_LABELS;
    }

    /**
     * Get model ID from preset name
     */
    public static function getModelId(string $preset): string
    {
        return self::MODELS[$preset] ?? $preset;
    }
}
# Framework Infrastructure and Directory System

This section provides a comprehensive overview of the framework's architecture, outlining the purpose of its main directories and the overall file structure.

## Core Concepts

The framework is organized into several key directories, each with a specific responsibility. This separation of concerns is crucial for maintaining a clean and scalable codebase.

### `api/` - API Endpoints
_Handles all API requests in a controller-only MVC structure._

### `app/` - Application Core & Business Logic
_The heart of the application, replacing traditional models. Contains business logic, database interactions, and reusable components._

### `public/` - User Interface
_The document root for the application's UI, containing controllers, views, and static assets._

### `commands/` - CLI Tasks
_Houses command-line scripts for automated tasks, cron jobs, and internal server processing._

### `core/` - Framework Core
_Contains the foundational framework files that should rarely be modified._

## Directory Structure
```
/www/wwwroot/(project_directory)/
├── api/
├── app/
├── commands/
├── core/
├── plugins/
├── public/
├── templates/
└── vendor/
```

### Detailed `app/` Directory Structure
```
app/
├── Helpers/
│   ├── DB.php          # Database operations
│   ├── Session.php     # Session management
│   ├── Validator.php   # Input validation
│   └── Auth.php        # Authentication helpers
├── Services/
│   ├── EmailService.php
│   ├── PaymentService.php
│   └── NotificationService.php
├── Libraries/
│   └── CustomAuth.php
└── Models/             # Optional: Domain models/entities
    └── Client.php
```

**Example Helper:**
```php
<?php
// app/Helpers/Validator.php
class Validator {
    public static function email($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function required($value) {
        return !empty(trim($value));
    }
    
    public static function minLength($value, $min) {
        return strlen($value) >= $min;
    }
}
```

**Example Service:**
```php
<?php
// app/Services/EmailService.php
class EmailService {
    public static function sendWelcomeEmail($email, $name) {
        $template = file_get_contents(TEMPLATES_PATH . '/emails/welcome.html');
        $template = str_replace('%FNAME%', $name, $template);
        
        // Use PHPMailer or your email provider
        // Return true/false based on success
    }
}
```

## Quick Reference

### File Naming Conventions
- **Controllers:** `PascalCase.php` (e.g., `ClientReports.php`)
- **Views:** `snake_case/` folders, `snake_case.php` files (e.g., `client_reports/index.php`)
- **Commands:** `PascalCaseCommand.php` (e.g., `SyncDataCommand.php`)
- **Helpers:** `PascalCase.php` (e.g., `Validator.php`)
- **Services:** `PascalCaseService.php` (e.g., `EmailService.php`)

### Common Database Patterns
```php
// Fetch all records
$clients = Db::select("SELECT * FROM clients WHERE active = :active", [':active' => 1]);

// Fetch single record
$client = Db::getRow("SELECT * FROM clients WHERE id = :id", [':id' => $id]);

// Insert record
$clientId = Db::insert('clients', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'created_at' => date('Y-m-d H:i:s')
]);

// Update record
$affected = Db::update('clients', 
    ['name' => 'Jane Doe', 'updated_at' => date('Y-m-d H:i:s')], 
    'id = :id', 
    [':id' => $id]
);

// Delete record
$affected = Db::delete('clients', 'id = :id', [':id' => $id]);

// Execute custom query
Db::execute("UPDATE clients SET status = :status WHERE last_login < :date", [
    ':status' => 'inactive',
    ':date' => date('Y-m-d', strtotime('-1 year'))
]);

// Transactions
Db::beginTransaction();
try {
    Db::insert('orders', ['client_id' => $clientId, 'total' => 100]);
    Db::update('clients', ['balance' => 'balance - 100'], 'id = :id', [':id' => $clientId]);
    Db::commit();
} catch (Exception $e) {
    Db::rollback();
    throw $e;
}
```

### Common Controller Patterns
```php
// Render a view with data
$this->view->data['clients'] = $clients;
$this->view->render('clients/index', true, 'site');

// Render without wrapper (e.g., login page)
$this->view->render('auth/login', false);

// Redirect
redirect('/clients');

// JSON response
header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $client]);

// 404 response
http_response_code(404);
$this->view->render('errors/404', true, 'site');
```

# Developer Workflows & Tutorials

This section provides practical, step-by-step guides for common development tasks within the framework.

## Request Lifecycle Overview

Understanding the request lifecycle is key to knowing how the framework processes a request and generates a response.

1.  **Entry Point:** A user request first hits either `public/index.php` (for UI) or `api/index.php` (for API).
2.  **Bootstrapping:** The `core/Bootstrap.php` file is initiated. It sets up the environment, loads the configuration from `config.php`, and initializes necessary services.
3.  **Routing:** The incoming URL is processed by the routing engine.
    *   For simple URLs, **auto-routing** maps the URL directly to a `Controller/method` pair (e.g., `/clients/show` maps to `Clients::show()`).
    *   For complex URLs defined in the routing configuration, **AltoRouter** matches the pattern and executes the associated callback or controller.
4.  **Controller Execution:** The matched controller method is called.
5.  **Business Logic:** The controller interacts with the `app/` directory (e.g., calling helpers like `Db::select()`) to fetch data or perform business logic.
6.  **Response Generation:**
    *   **UI (Public):** The controller calls `$this->view->render()`, passing data to a view file. The view is rendered within a wrapper (if specified), and the final HTML is sent to the browser.
    *   **API:** The controller typically returns data encoded as a JSON string and sets the appropriate `Content-Type` header.

## Tutorial: Creating a New Page (UI)

This tutorial walks through creating a "Clients" page that displays a list of clients.

### Step 1: Create the Controller

Create a new file at `public/controllers/Clients.php`. This controller will handle the logic for the clients page.
```php
<?php
// public/controllers/Clients.php
class Clients extends Controller
{
    public function __construct() {
        parent::__construct();
        // Optional: Add authentication check
        // if (!Auth::isLoggedIn()) {
        //     redirect('/login');
        // }
    }
    
    public function index()
    {
        // Fetch data using a helper from the app/ directory
        $clients = Db::select("SELECT * FROM clients ORDER BY name ASC");

        // Pass data to the view
        $this->view->data['clients'] = $clients;
        $this->view->data['page_title'] = 'Our Clients';

        // Render the view
        $this->view->render('clients/index', true, 'site');
    }
    
    public function show($id)
    {
        // Fetch single client
        $client = Db::getRow("SELECT * FROM clients WHERE id = :id", [':id' => $id]);
        
        if (!$client) {
            http_response_code(404);
            $this->view->render('errors/404', true, 'site');
            return;
        }
        
        // Fetch related data
        $orders = Db::select("SELECT * FROM orders WHERE client_id = :id ORDER BY created_at DESC", 
            [':id' => $id]
        );
        
        $this->view->data['client'] = $client;
        $this->view->data['orders'] = $orders;
        $this->view->data['page_title'] = $client['name'];
        
        $this->view->render('clients/show', true, 'site');
    }
    
    public function create()
    {
        // Show create form
        $this->view->data['page_title'] = 'New Client';
        $this->view->render('clients/create', true, 'site');
    }
    
    public function store()
    {
        // Handle form submission
        try {
            // Validate input
            if (!Validator::required($_POST['name'])) {
                throw new Exception('Name is required');
            }
            if (!Validator::email($_POST['email'])) {
                throw new Exception('Valid email is required');
            }
            
            // Insert into database
            $clientId = Db::insert('clients', [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Optional: Send welcome email
            // EmailService::sendWelcomeEmail($_POST['email'], $_POST['name']);
            
            // Redirect to the new client's page
            redirect('/clients/show/' . $clientId);
            
        } catch (Exception $e) {
            // Handle errors
            if (DEBUG) {
                echo $e->getMessage();
            } else {
                $this->view->data['error'] = 'Could not create client. Please try again.';
                $this->view->data['form_data'] = $_POST;
                $this->view->render('clients/create', true, 'site');
            }
        }
    }
    
    public function edit($id)
    {
        $client = Db::getRow("SELECT * FROM clients WHERE id = :id", [':id' => $id]);
        
        if (!$client) {
            http_response_code(404);
            $this->view->render('errors/404', true, 'site');
            return;
        }
        
        $this->view->data['client'] = $client;
        $this->view->data['page_title'] = 'Edit ' . $client['name'];
        $this->view->render('clients/edit', true, 'site');
    }
    
    public function update($id)
    {
        try {
            // Validate
            if (!Validator::required($_POST['name'])) {
                throw new Exception('Name is required');
            }
            
            // Update
            Db::update('clients', [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'phone' => $_POST['phone'] ?? null,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = :id', [':id' => $id]);
            
            redirect('/clients/show/' . $id);
            
        } catch (Exception $e) {
            if (DEBUG) {
                echo $e->getMessage();
            } else {
                $this->view->data['error'] = 'Could not update client. Please try again.';
                $this->view->data['client'] = $_POST;
                $this->view->data['client']['id'] = $id;
                $this->view->render('clients/edit', true, 'site');
            }
        }
    }
    
    public function delete($id)
    {
        try {
            Db::delete('clients', 'id = :id', [':id' => $id]);
            redirect('/clients');
        } catch (Exception $e) {
            if (DEBUG) {
                echo $e->getMessage();
            } else {
                redirect('/clients/show/' . $id . '?error=delete_failed');
            }
        }
    }
}
```

### Step 2: Create the Views

Create view files at `public/views/clients/`:

**Index View (`public/views/clients/index.php`):**
```php
<div class="container">
    <h1><?php echo $this->data['page_title']; ?></h1>
    
    <a href="/clients/create" class="btn btn-primary">Add New Client</a>
    
    <?php if (empty($this->data['clients'])): ?>
        <p>No clients found.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->data['clients'] as $client): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['name']); ?></td>
                        <td><?php echo htmlspecialchars($client['email']); ?></td>
                        <td><?php echo htmlspecialchars($client['phone'] ?? '-'); ?></td>
                        <td>
                            <a href="/clients/show/<?php echo $client['id']; ?>">View</a>
                            <a href="/clients/edit/<?php echo $client['id']; ?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
```

**Show View (`public/views/clients/show.php`):**
```php
<div class="container">
    <h1><?php echo htmlspecialchars($this->data['client']['name']); ?></h1>
    
    <div class="client-details">
        <p><strong>Email:</strong> <?php echo htmlspecialchars($this->data['client']['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($this->data['client']['phone'] ?? '-'); ?></p>
    </div>
    
    <div class="actions">
        <a href="/clients/edit/<?php echo $this->data['client']['id']; ?>" class="btn btn-primary">Edit</a>
        <a href="/clients" class="btn btn-secondary">Back to List</a>
    </div>
    
    <h2>Orders</h2>
    <?php if (empty($this->data['orders'])): ?>
        <p>No orders yet.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Total</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->data['orders'] as $order): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td>$<?php echo number_format($order['total'], 2); ?></td>
                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
```

**Create/Edit View (`public/views/clients/create.php`):**
```php
<div class="container">
    <h1><?php echo $this->data['page_title']; ?></h1>
    
    <?php if (isset($this->data['error'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($this->data['error']); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="/clients/store">
        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" class="form-control" 
                   value="<?php echo htmlspecialchars($this->data['form_data']['name'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" class="form-control" 
                   value="<?php echo htmlspecialchars($this->data['form_data']['email'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone" class="form-control" 
                   value="<?php echo htmlspecialchars($this->data['form_data']['phone'] ?? ''); ?>">
        </div>
        
        <button type="submit" class="btn btn-primary">Create Client</button>
        <a href="/clients" class="btn btn-secondary">Cancel</a>
    </form>
</div>
```

### Step 3: Create Page-Specific Assets

Create the corresponding CSS and JavaScript files for this page.

*   `public/assets/css/clients.css`
*   `public/assets/js/clients.js`

These will be loaded by the site's wrapper.

### Step 4: Access Your New Page

You can now access your new page by navigating to:
- `http://yoursite.com/clients` - List all clients
- `http://yoursite.com/clients/show/1` - View client #1
- `http://yoursite.com/clients/create` - Create new client form
- `http://yoursite.com/clients/edit/1` - Edit client #1

The auto-routing will automatically map these URLs to the corresponding methods in your `Clients` controller.

## Tutorial: Creating a New API Endpoint

This tutorial shows how to create API endpoints for product management.

### Step 1: Create the API Controller

Create a new file at `api/controllers/Products.php`.
```php
<?php
// api/controllers/Products.php
class Products
{
    /**
     * GET /api/products/list
     * Get all products with optional filtering
     */
    public function list()
    {
        header('Content-Type: application/json');
        
        try {
            // Get query parameters
            $category = $_GET['category'] ?? null;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            // Build query
            $query = "SELECT * FROM products WHERE 1=1";
            $params = [];
            
            if ($category) {
                $query .= " AND category = :category";
                $params[':category'] = $category;
            }
            
            $query .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
            
            $products = Db::select($query, $params);
            
            echo json_encode([
                'success' => true,
                'data' => $products,
                'count' => count($products)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => DEBUG ? $e->getMessage() : 'Internal server error'
            ]);
        }
    }
    
    /**
     * GET /api/products/get/{id}
     * Get a single product by ID
     */
    public function get($id)
    {
        header('Content-Type: application/json');
        
        try {
            $product = Db::getRow("SELECT * FROM products WHERE id = :id", [':id' => $id]);
            
            if ($product) {
                echo json_encode([
                    'success' => true,
                    'data' => $product
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Product not found'
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => DEBUG ? $e->getMessage() : 'Internal server error'
            ]);
        }
    }
    
    /**
     * POST /api/products/create
     * Create a new product
     */
    public function create()
    {
        header('Content-Type: application/json');
        
        try {
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validate
            if (!isset($input['name']) || !isset($input['price'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Name and price are required'
                ]);
                return;
            }
            
            // Insert
            $productId = Db::insert('products', [
                'name' => $input['name'],
                'description' => $input['description'] ?? null,
                'price' => $input['price'],
                'category' => $input['category'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'data' => ['id' => $productId]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => DEBUG ? $e->getMessage() : 'Internal server error'
            ]);
        }
    }
    
    /**
     * PUT /api/products/update/{id}
     * Update an existing product
     */
    public function update($id)
    {
        header('Content-Type: application/json');
        
        try {
            // Check if product exists
            $product = Db::getRow("SELECT * FROM products WHERE id = :id", [':id' => $id]);
            
            if (!$product) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Product not found'
                ]);
                return;
            }
            
            // Get JSON input
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Build update data
            $updateData = [];
            if (isset($input['name'])) $updateData['name'] = $input['name'];
            if (isset($input['description'])) $updateData['description'] = $input['description'];
            if (isset($input['price'])) $updateData['price'] = $input['price'];
            if (isset($input['category'])) $updateData['category'] = $input['category'];
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            
            // Update
            Db::update('products', $updateData, 'id = :id', [':id' => $id]);
            
            echo json_encode([
                'success' => true,
                'data' => ['id' => $id]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => DEBUG ? $e->getMessage() : 'Internal server error'
            ]);
        }
    }
    
    /**
     * DELETE /api/products/delete/{id}
     * Delete a product
     */
    public function delete($id)
    {
        header('Content-Type: application/json');
        
        try {
            // Check if product exists
            $product = Db::getRow("SELECT * FROM products WHERE id = :id", [':id' => $id]);
            
            if (!$product) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Product not found'
                ]);
                return;
            }
            
            // Delete
            Db::delete('products', 'id = :id', [':id' => $id]);
            
            echo json_encode([
                'success' => true,
                'data' => ['id' => $id]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => DEBUG ? $e->getMessage() : 'Internal server error'
            ]);
        }
    }
}
```

### Step 2: Access the API Endpoints

You can now make requests to these endpoints:

**Example using cURL:**
```sh
# List all products
curl http://yoursite.com/api/products/list

# List products with filtering
curl "http://yoursite.com/api/products/list?category=electronics&limit=10"

# Get product with ID 123
curl http://yoursite.com/api/products/get/123

# Create a new product
curl -X POST http://yoursite.com/api/products/create \
  -H "Content-Type: application/json" \
  -d '{"name":"New Product","price":29.99,"category":"electronics"}'

# Update product
curl -X PUT http://yoursite.com/api/products/update/123 \
  -H "Content-Type: application/json" \
  -d '{"price":24.99}'

# Delete product
curl -X DELETE http://yoursite.com/api/products/delete/123
```

If `SECUREAPI` is enabled in `config.php`, you must include the authorization token in the header:
```sh
# Get the authorization key from the AUTHORIZATION define in config.php
API_KEY="your_secret_api_key"

# Example with authorization
curl -H "Authorization: $API_KEY" http://yoursite.com/api/products/get/123
```

**Example using JavaScript (Fetch API):**
```javascript
// GET request
fetch('/api/products/list?category=electronics')
  .then(response => response.json())
  .then(data => console.log(data));

// POST request
fetch('/api/products/create', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    // Include if SECUREAPI is enabled
    // 'Authorization': 'your_secret_api_key'
  },
  body: JSON.stringify({
    name: 'New Product',
    price: 29.99,
    category: 'electronics'
  })
})
  .then(response => response.json())
  .then(data => console.log(data));
```

## Tutorial: Creating and Running a CLI Command

This tutorial shows how to create command-line tasks for various purposes.

### Step 1: Create the Command File

**Simple Command (`commands/SyncDataCommand.php`):**
```php
<?php
// commands/SyncDataCommand.php

echo "=== Data Sync Command ===\n";

// You can use arguments passed from the command line
$options = getopt("", ["date:", "force"]);
$syncDate = $options['date'] ?? 'today';
$forceSync = isset($options['force']);

echo "Starting data sync for: " . $syncDate . "\n";
if ($forceSync) {
    echo "Force mode enabled\n";
}

try {
    // Example: Sync data from external API
    $externalData = file_get_contents('https://api.example.com/data');
    $data = json_decode($externalData, true);
    
    foreach ($data as $record) {
        // Check if record exists
        $exists = Db::getRow("SELECT id FROM synced_data WHERE external_id = :id", 
            [':id' => $record['id']]
        );
        
        if ($exists && !$forceSync) {
            echo "Skipping existing record: {$record['id']}\n";
            continue;
        }
        
        if ($exists) {
            // Update existing
            Db::update('synced_data', [
                'data' => json_encode($record),
                'synced_at' => date('Y-m-d H:i:s')
            ], 'external_id = :id', [':id' => $record['id']]);
            echo "Updated record: {$record['id']}\n";
        } else {
            // Insert new
            Db::insert('synced_data', [
                'external_id' => $record['id'],
                'data' => json_encode($record),
                'synced_at' => date('Y-m-d H:i:s')
            ]);
            echo "Inserted record: {$record['id']}\n";
        }
    }
    
    echo "\nData sync completed successfully.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
```

**Email Queue Processor (`commands/ProcessEmailQueueCommand.php`):**
```php
<?php
// commands/ProcessEmailQueueCommand.php

echo "=== Processing Email Queue ===\n";

$options = getopt("", ["limit:"]);
$limit = $options['limit'] ?? 100;

try {
    // Get pending emails
    $emails = Db::select(
        "SELECT * FROM email_queue WHERE status = 'pending' LIMIT :limit",
        [':limit' => (int)$limit]
    );
    
    echo "Found " . count($emails) . " pending emails\n";
    
    $sent = 0;
    $failed = 0;
    
    foreach ($emails as $email) {
        try {
            // Send email using your EmailService
            EmailService::send(
                $email['to'],
                $email['subject'],
                $email['body']
            );
            
            // Update status
            Db::update('email_queue', [
                'status' => 'sent',
                'sent_at' => date('Y-m-d H:i:s')
            ], 'id = :id', [':id' => $email['id']]);
            
            $sent++;
            echo ".";
            
        } catch (Exception $e) {
            // Mark as failed
            Db::update('email_queue', [
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'failed_at' => date('Y-m-d H:i:s')
            ], 'id = :id', [':id' => $email['id']]);
            
            $failed++;
            echo "F";
        }
    }
    
    echo "\n\nResults:\n";
    echo "Sent: $sent\n";
    echo "Failed: $failed\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
```

**Database Cleanup Command (`commands/CleanupDatabaseCommand.php`):**
```php
<?php
// commands/CleanupDatabaseCommand.php

echo "=== Database Cleanup ===\n";

$options = getopt("", ["days:", "dry-run"]);
$days = $options['days'] ?? 30;
$dryRun = isset($options['dry-run']);

if ($dryRun) {
    echo "DRY RUN MODE - No changes will be made\n";
}

try {
    $cutoffDate = date('Y-m-d', strtotime("-{$days} days"));
    echo "Cleaning up records older than: $cutoffDate\n\n";
    
    // Clean old logs
    $oldLogs = Db::select("SELECT COUNT(*) as count FROM logs WHERE created_at < :date", 
        [':date' => $cutoffDate]
    );
    echo "Logs to delete: " . $oldLogs[0]['count'] . "\n";
    
    if (!$dryRun) {
        $deleted = Db::delete('logs', 'created_at < :date', [':date' => $cutoffDate]);
        echo "Deleted: $deleted logs\n";
    }
    
    // Clean soft-deleted records
    $oldDeleted = Db::select("SELECT COUNT(*) as count FROM clients WHERE deleted_at IS NOT NULL AND deleted_at < :date", 
        [':date' => $cutoffDate]
    );
    echo "\nSoft-deleted clients to remove: " . $oldDeleted[0]['count'] . "\n";
    
    if (!$dryRun) {
        $deleted = Db::delete('clients', 'deleted_at IS NOT NULL AND deleted_at < :date', 
            [':date' => $cutoffDate]
        );
        echo "Deleted: $deleted clients\n";
    }
    
    echo "\nCleanup completed.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
```

### Step 2: Run the Commands

Execute commands from the root directory of your project using the `console` entry point.
```sh
# Run data sync
php console SyncDataCommand

# Run with options
php console SyncDataCommand --date="2024-10-28" --force

# Process email queue
php console ProcessEmailQueueCommand --limit=50

# Database cleanup (dry run first)
php console CleanupDatabaseCommand --days=60 --dry-run

# Then run for real
php console CleanupDatabaseCommand --days=60
```

### Step 3: Set Up Cron Jobs

Add these to your crontab for automated execution:
```sh
# Edit crontab
crontab -e

# Add these lines:
# Process email queue every 5 minutes
*/5 * * * * cd /www/wwwroot/(project_directory) && php console ProcessEmailQueueCommand >> /var/log/email-queue.log 2>&1

# Sync data every hour
0 * * * * cd /www/wwwroot/(project_directory) && php console SyncDataCommand >> /var/log/data-sync.log 2>&1

# Cleanup database every night at 2 AM
0 2 * * * cd /www/wwwroot/(project_directory) && php console CleanupDatabaseCommand --days=30 >> /var/log/cleanup.log 2>&1
```

## Error Handling Patterns

### Controller Error Handling
```php
<?php
class Clients extends Controller
{
    public function create()
    {
        try {
            // Start transaction for complex operations
            Db::beginTransaction();
            
            // Validate input
            $this->validateClientData($_POST);
            
            // Insert client
            $clientId = Db::insert('clients', [
                'name' => $_POST['name'],
                'email' => $_POST['email']
            ]);
            
            // Insert related data
            Db::insert('client_metadata', [
                'client_id' => $clientId,
                'source' => $_POST['source'] ?? 'website'
            ]);
            
            // Commit transaction
            Db::commit();
            
            // Send notification email
            try {
                EmailService::sendWelcomeEmail($_POST['email'], $_POST['name']);
            } catch (Exception $e) {
                // Log email failure but don't fail the whole operation
                error_log("Failed to send welcome email: " . $e->getMessage());
            }
            
            redirect('/clients/show/' . $clientId);
            
        } catch (ValidationException $e) {
            // Validation errors - show to user
            Db::rollback();
            $this->view->data['error'] = $e->getMessage();
            $this->view->data['form_data'] = $_POST;
            $this->view->render('clients/create', true, 'site');
            
        } catch (Exception $e) {
            // Other errors
            Db::rollback();
            
            if (DEBUG) {
                // Show full error in development
                echo "<pre>";
                echo "Error: " . $e->getMessage() . "\n";
                echo "File: " . $e->getFile() . "\n";
                echo "Line: " . $e->getLine() . "\n";
                echo "\nStack Trace:\n" . $e->getTraceAsString();
                echo "</pre>";
            } else {
                // Generic error in production
                $this->view->data['error'] = 'An error occurred. Please try again.';
                $this->view->data['form_data'] = $_POST;
                $this->view->render('clients/create', true, 'site');
                
                // Log the error
                error_log("Client creation failed: " . $e->getMessage());
            }
        }
    }
    
    private function validateClientData($data)
    {
        if (!Validator::required($data['name'] ?? '')) {
            throw new ValidationException('Name is required');
        }
        
        if (!Validator::email($data['email'] ?? '')) {
            throw new ValidationException('Valid email is required');
        }
        
        // Check for duplicate email
        $existing = Db::getRow("SELECT id FROM clients WHERE email = :email", 
            [':email' => $data['email']]
        );
        
        if ($existing) {
            throw new ValidationException('A client with this email already exists');
        }
    }
}

// Custom exception for validation errors
class ValidationException extends Exception {}
```

### API Error Handling
```php
<?php
class Products
{
    public function create()
    {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new ApiException('Invalid JSON', 400);
            }
            
            $this->validateProduct($input);
            
            $productId = Db::insert('products', [
                'name' => $input['name'],
                'price' => $input['price'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'data' => ['id' => $productId]
            ]);
            
        } catch (ApiException $e) {
            http_response_code($e->getCode());
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => DEBUG ? $e->getMessage() : 'Internal server error'
            ]);
            
            error_log("API Error: " . $e->getMessage());
        }
    }
    
    private function validateProduct($data)
    {
        if (!isset($data['name']) || empty(trim($data['name']))) {
            throw new ApiException('Product name is required', 400);
        }
        
        if (!isset($data['price']) || !is_numeric($data['price'])) {
            throw new ApiException('Valid price is required', 400);
        }
        
        if ($data['price'] < 0) {
            throw new ApiException('Price cannot be negative', 400);
        }
    }
}

// Custom exception for API errors
class ApiException extends Exception {
    public function __construct($message, $code = 400) {
        parent::__construct($message, $code);
    }
}
```

### Command Error Handling
```php
<?php
// commands/ProcessOrdersCommand.php

echo "=== Processing Orders ===\n";

$errors = [];
$processed = 0;

try {
    $orders = Db::select("SELECT * FROM orders WHERE status = 'pending'");
    
    foreach ($orders as $order) {
        try {
            // Process order
            processOrder($order);
            $processed++;
            echo ".";
            
        } catch (Exception $e) {
            // Log individual order failure but continue processing
            $errors[] = [
                'order_id' => $order['id'],
                'error' => $e->getMessage()
            ];
            echo "E";
        }
    }
    
    echo "\n\n";
    echo "Processed: $processed\n";
    echo "Errors: " . count($errors) . "\n";
    
    if (!empty($errors)) {
        echo "\nFailed Orders:\n";
        foreach ($errors as $error) {
            echo "Order #{$error['order_id']}: {$error['error']}\n";
        }
        exit(1); // Exit with error code
    }
    
} catch (Exception $e) {
    echo "\nFATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

function processOrder($order) {
    // Order processing logic
    // Throw exceptions on failures
}
```

# Common Configurations & Features

## `config.php` - Framework Configuration

The `config.php` file is the central hub for all framework-wide configurations. It handles security-related keys, API keys, debugging settings, and various integration parameters.

### Security Configurations

It is **highly recommended** to change all security-related values for each new project.

*   **`define('DEBUG', false);`**: Set to `true` to enable error reporting for development. **Always set to `false` in production.**
*   **`define('SECUREAPI', false);`**: Set to `true` to require an authorization key for API access.
*   **`define('AUTHORIZATION', 'your_secret_api_key');`**: The secret key required when `SECUREAPI` is true. **Always change this to a strong, random value.**

**Example: Generating a secure API key**
```php
// Generate a random API key
define('AUTHORIZATION', bin2hex(random_bytes(32)));
```

### Database Configuration
```php
// Database settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');
```

### API Keys and Integrations

All third-party API keys (Stripe, PHPMailer, etc.) are managed in this file, making them globally accessible via their defined constants.
```php
// Stripe
define('STRIPE_SECRET_KEY', 'sk_test_...');
define('STRIPE_PUBLIC_KEY', 'pk_test_...');

// Email (PHPMailer)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_FROM', 'noreply@yoursite.com');
define('SMTP_FROM_NAME', 'Your Site Name');

// AWS S3
define('AWS_KEY', 'your-aws-key');
define('AWS_SECRET', 'your-aws-secret');
define('AWS_REGION', 'us-east-1');
define('AWS_BUCKET', 'your-bucket-name');

// Other services
define('RECAPTCHA_SITE_KEY', 'your-site-key');
define('RECAPTCHA_SECRET_KEY', 'your-secret-key');
```

### Path Constants
```php
// Define base paths
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('TEMPLATES_PATH', ROOT_PATH . '/templates');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
```

### Environment-Specific Configuration
```php
// Determine environment
define('ENVIRONMENT', getenv('APP_ENV') ?: 'production');

// Environment-specific settings
if (ENVIRONMENT === 'development') {
    define('DEBUG', true);
    define('BASE_URL', 'http://localhost:8000');
} else {
    define('DEBUG', false);
    define('BASE_URL', 'https://yoursite.com');
}
```

## .htaccess and SSL

The root `.htaccess` file includes a rule for automatic redirection to HTTPS. It is recommended to temporarily disable this rule when setting up a new SSL certificate (e.g., with Let's Encrypt) to avoid issues with the verification process.
```apache
# Force HTTPS
# Comment out these lines when setting up SSL certificate
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Standard routing
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

## Public Assets Structure

The `public/assets` directory is organized to promote performance and streamline debugging by using page-specific CSS and JavaScript files.
```
public/assets/
├── css/
│   ├── global.css          # Site-wide styles
│   ├── clients.css         # Page-specific: clients
│   ├── products.css        # Page-specific: products
│   └── dashboard.css       # Page-specific: dashboard
├── js/
│   ├── global.js           # Site-wide scripts
│   ├── clients.js          # Page-specific: clients
│   ├── products.js         # Page-specific: products
│   └── dashboard.js        # Page-specific: dashboard
└── images/
    ├── logo.png
    └── icons/
```

**Wrapper automatically loads page-specific assets:**
```php
<!-- In templates/wrappers/site/header.php -->
<link rel="stylesheet" href="/assets/css/global.css">
<?php
// Auto-load page-specific CSS if it exists
$controller = strtolower($this->getControllerName());
if (file_exists(PUBLIC_PATH . "/assets/css/{$controller}.css")) {
    echo "<link rel='stylesheet' href='/assets/css/{$controller}.css'>";
}
?>
```

## View System

### Wrappers (Header & Footer)

A "wrapper" is the main site template (`header.php` and `footer.php`). You can control which wrapper to use or disable it entirely, which is useful for pages like a login screen.

**Wrapper Location:**
```
templates/wrappers/
├── site/               # Main site wrapper
│   ├── header.php
│   └── footer.php
├── admin/              # Admin panel wrapper
│   ├── header.php
│   └── footer.php
└── minimal/            # Minimal wrapper (login, etc.)
    ├── header.php
    └── footer.php
```

**Usage Examples:**
```php
// Use default 'site' wrapper
$this->view->render('clients/index', true, 'site');

// Use admin wrapper
$this->view->render('admin/dashboard', true, 'admin');

// Use minimal wrapper (login page)
$this->view->render('auth/login', true, 'minimal');

// No wrapper at all
$this->view->render('auth/login', false);
```

**Example Wrapper (`templates/wrappers/site/header.php`):**
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $this->data['page_title'] ?? 'My Site'; ?></title>
    
    <!-- Global CSS -->
    <link rel="stylesheet" href="/assets/css/global.css">
    
    <!-- Page-specific CSS -->
    <?php
    $controller = strtolower($this->getControllerName());
    if (file_exists(PUBLIC_PATH . "/assets/css/{$controller}.css")) {
        echo "<link rel='stylesheet' href='/assets/css/{$controller}.css'>";
    }
    ?>
</head>
<body>
    <nav class="navbar">
        <a href="/">Home</a>
        <a href="/clients">Clients</a>
        <a href="/products">Products</a>
    </nav>
    
    <main class="container">
```

**Example Footer (`templates/wrappers/site/footer.php`):**
```php
    </main>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> My Site</p>
    </footer>
    
    <!-- Global JS -->
    <script src="/assets/js/global.js"></script>
    
    <!-- Page-specific JS -->
    <?php
    $controller = strtolower($this->getControllerName());
    if (file_exists(PUBLIC_PATH . "/assets/js/{$controller}.js")) {
        echo "<script src='/assets/js/{$controller}.js'></script>";
    }
    ?>
</body>
</html>
```

### Partial Views

Partial views allow you to load reusable HTML snippets (like sidebars or breadcrumbs) into other views, similar to `require_once()`.

**Partial Location:**
```
public/views/
├── _partials/
│   ├── breadcrumb.php
│   ├── sidebar.php
│   ├── pagination.php
│   └── client_card.php
```

**Usage in Views:**
```php
<!-- public/views/clients/show.php -->
<div class="container">
    <?php $this->partial('_partials/breadcrumb', [
        'Home' => '/',
        'Clients' => '/clients',
        $this->data['client']['name'] => ''
    ]); ?>
    
    <div class="row">
        <div class="col-md-8">
            <h1><?php echo htmlspecialchars($this->data['client']['name']); ?></h1>
            <!-- Main content -->
        </div>
        
        <div class="col-md-4">
            <?php $this->partial('_partials/sidebar'); ?>
        </div>
    </div>
</div>
```

**Example Partial (`public/views/_partials/breadcrumb.php`):**
```php
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <?php
        $breadcrumb = $this->data['breadcrumb'] ?? [];
        $lastItem = array_key_last($breadcrumb);
        
        foreach ($breadcrumb as $label => $url):
            if ($label === $lastItem):
                echo '<li class="breadcrumb-item active" aria-current="page">' . 
                     htmlspecialchars($label) . '</li>';
            else:
                echo '<li class="breadcrumb-item"><a href="' . $url . '">' . 
                     htmlspecialchars($label) . '</a></li>';
            endif;
        endforeach;
        ?>
    </ol>
</nav>
```

## Routing

The framework uses auto-routing by convention (`/controller/method`) for most cases. For more complex needs, it integrates `AltoRouter`.

### Auto-Routing Examples
```
URL: /clients
Maps to: Clients::index()

URL: /clients/show/123
Maps to: Clients::show(123)

URL: /products/edit/456
Maps to: Products::edit(456)
```

### AltoRouter for Complex Routes

Define custom routes in `public/routes.php` or `api/routes.php`:
```php
<?php
// public/routes.php (or wherever you configure AltoRouter)

// Named route with parameter
$router->map('GET', '/users/[i:id]', 'Users#show', 'user_show');

// Multiple parameters
$router->map('GET', '/blog/[i:year]/[i:month]/[*:slug]', 'Blog#show', 'blog_post');

// POST route
$router->map('POST', '/api/users/create', 'UsersAPI#create', 'api_user_create');

// Optional parameter
$router->map('GET', '/products/category/[a:category]?', 'Products#category', 'products_category');
```

**Match Types:**
*   **`[i:id]`**: Match an integer as parameter 'id'
*   **`[a:action]`**: Match alphanumeric characters (A-Z, a-z, 0-9, -)
*   **`[h:key]`**: Match hexadecimal characters
*   **`[*:trailing]`**: Catch-all for the rest of the URL path
*   **`[**:path]`**: Match everything including slashes

**Using Named Routes:**
```php
// Generate URL for a named route
echo $router->generate('user_show', ['id' => 5]); 
// Outputs: /users/5

echo $router->generate('blog_post', [
    'year' => 2024,
    'month' => 10,
    'slug' => 'my-first-post'
]);
// Outputs: /blog/2024/10/my-first-post
```

**In Controllers:**
```php
<?php
class Users extends Controller
{
    public function show($id)
    {
        // Access route parameters
        $user = Db::getRow("SELECT * FROM users WHERE id = :id", [':id' => $id]);
        
        $this->view->data['user'] = $user;
        $this->view->render('users/show', true, 'site');
    }
}
```

## `templates/` - Reusable HTML Documents

This directory holds reusable HTML documents, such as transactional emails (`/templates/emails/`). These templates use placeholders like `%FNAME%` that are replaced with dynamic data by PHP before being sent.

**Template Structure:**
```
templates/
├── emails/
│   ├── welcome.html
│   ├── password_reset.html
│   ├── invoice.html
│   └── notification.html
└── wrappers/
    ├── site/
    ├── admin/
    └── minimal/
```

**Example Email Template (`templates/emails/welcome.html`):**
```html
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { background: #007bff; color: white; padding: 20px; }
        .content { padding: 20px; }
        .button { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to %SITE_NAME%</h1>
        </div>
        <div class="content">
            <p>Hi %FNAME%,</p>
            <p>Thank you for signing up! We're excited to have you on board.</p>
            <p>Your account email is: <strong>%EMAIL%</strong></p>
            <p>
                <a href="%VERIFY_LINK%" class="button">Verify Your Email</a>
            </p>
            <p>Thanks,<br>The %SITE_NAME% Team</p>
        </div>
    </div>
</body>
</html>
```

**Using Templates in Code:**
```php
<?php
// app/Services/EmailService.php
class EmailService
{
    public static function sendWelcomeEmail($email, $firstName)
    {
        // Load template
        $template = file_get_contents(TEMPLATES_PATH . '/emails/welcome.html');
        
        // Replace placeholders
        $replacements = [
            '%FNAME%' => $firstName,
            '%EMAIL%' => $email,
            '%SITE_NAME%' => 'My Awesome Site',
            '%VERIFY_LINK%' => BASE_URL . '/verify?token=' . self::generateToken($email)
        ];
        
        $body = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );
        
        // Send email (using PHPMailer or other)
        return self::send($email, 'Welcome to My Awesome Site', $body);
    }
    
    private static function send($to, $subject, $body)
    {
        // PHPMailer implementation
        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            
            $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
            $mail->addAddress($to);
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Email send failed: " . $mail->ErrorInfo);
            return false;
        }
    }
}
```

## `plugins/` - Extendable Functionality (In Development)

This directory is for modular, self-contained add-ons (like a CRM or weather widget) that can extend the framework's functionality, similar to WordPress plugins.

**Planned Structure:**
```
plugins/
├── crm/
│   ├── plugin.php          # Plugin initialization
│   ├── controllers/
│   ├── views/
│   ├── assets/
│   └── README.md
├── weather-widget/
│   ├── plugin.php
│   ├── WeatherWidget.php
│   └── assets/
└── analytics/
    ├── plugin.php
    ├── services/
    └── config.php
```

# Core Classes Reference

## Controller (`core/Controller.php`)

Base controller class that all public controllers extend.

**Properties:**
- `$this->view` - View renderer instance

**Methods:**
- `redirect($url)` - Perform HTTP redirect
- `getControllerName()` - Get current controller name
- `getMethodName()` - Get current method name

**Example:**
```php
<?php
class Clients extends Controller
{
    public function __construct() {
        parent::__construct();
        // Initialization logic
    }
    
    public function index() {
        // Will be available as $this->data in the view
        $this->view->data['clients'] = Db::select("SELECT * FROM clients");
        $this->view->render('clients/index', true, 'site');
    }
    
    protected function requireAuth() {
        if (!Auth::isLoggedIn()) {
            $this->redirect('/login');
        }
    }
}
```

## View (`core/View.php`)

Handles view rendering and data passing.

**Properties:**
- `$this->data` - Array of variables to pass to view

**Methods:**
- `render($viewPath, $useWrapper = true, $wrapperName = 'site')` - Render a view
- `partial($partialPath, $data = [])` - Load a partial view
- `getControllerName()` - Get current controller name (for asset loading)

**Example:**
```php
// In controller
$this->view->data['title'] = 'My Page';
$this->view->data['users'] = $users;
$this->view->render('users/index', true, 'site');

// In view
<h1><?php echo $this->data['title']; ?></h1>
<?php foreach ($this->data['users'] as $user): ?>
    <p><?php echo htmlspecialchars($user['name']); ?></p>
<?php endforeach; ?>

// Using partials
<?php $this->partial('_partials/breadcrumb', [
    'breadcrumb' => ['Home' => '/', 'Users' => '']
]); ?>
```

## Db (`app/Helpers/DB.php`)

Static database helper class for all database operations.

**Connection Methods:**
- `Db::getConnection()` - Get PDO connection instance
- `Db::beginTransaction()` - Start transaction
- `Db::commit()` - Commit transaction
- `Db::rollback()` - Rollback transaction

**Query Methods:**
- `Db::select($query, $params = [])` - Execute SELECT and return all rows
- `Db::getRow($query, $params = [])` - Execute SELECT and return first row
- `Db::execute($query, $params = [])` - Execute any query
- `Db::insert($table, $data)` - Insert row and return last insert ID
- `Db::update($table, $data, $where, $whereParams = [])` - Update rows and return affected count
- `Db::delete($table, $where, $whereParams = [])` - Delete rows and return affected count

**Examples:**
```php
// Select multiple rows
$clients = Db::select("SELECT * FROM clients WHERE active = :active", [':active' => 1]);

// Select single row
$client = Db::getRow("SELECT * FROM clients WHERE id = :id", [':id' => $id]);

// Insert
$clientId = Db::insert('clients', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'created_at' => date('Y-m-d H:i:s')
]);

// Update
$affected = Db::update('clients', 
    ['name' => 'Jane Doe', 'updated_at' => date('Y-m-d H:i:s')],
    'id = :id',
    [':id' => $id]
);

// Delete
$affected = Db::delete('clients', 'id = :id', [':id' => $id]);

// Custom query
Db::execute("UPDATE clients SET last_login = NOW() WHERE id = :id", [':id' => $id]);

// Transactions
Db::beginTransaction();
try {
    Db::insert('orders', ['client_id' => 1, 'total' => 100]);
    Db::update('clients', ['balance' => 'balance - 100'], 'id = 1');
    Db::commit();
} catch (Exception $e) {
    Db::rollback();
    throw $e;
}
```

## Helper Classes

### Validator (`app/Helpers/Validator.php`)

Input validation helper.

**Methods:**
```php
Validator::required($value)           // Check if not empty
Validator::email($value)              // Validate email format
Validator::minLength($value, $min)    // Check minimum length
Validator::maxLength($value, $max)    // Check maximum length
Validator::numeric($value)            // Check if numeric
Validator::alpha($value)              // Check if alphabetic
Validator::alphanumeric($value)       // Check if alphanumeric
Validator::url($value)                // Validate URL format
Validator::date($value)               // Validate date format
```

### Session (`app/Helpers/Session.php`)

Session management helper.

**Methods:**
```php
Session::start()                      // Start session
Session::set($key, $value)            // Set session variable
Session::get($key, $default = null)   // Get session variable
Session::has($key)                    // Check if key exists
Session::delete($key)                 // Delete session variable
Session::destroy()                    // Destroy session
Session::flash($key, $value)          // Set flash message
Session::getFlash($key)               // Get and delete flash message
```

### Auth (`app/Helpers/Auth.php`)

Authentication helper.

**Methods:**
```php
Auth::login($userId)                  // Log in user
Auth::logout()                        // Log out user
Auth::isLoggedIn()                    // Check if user is logged in
Auth::getUserId()                     // Get current user ID
Auth::getUser()                       // Get current user data
Auth::check($permission)              // Check user permission
```

# Database Usage

The `Db` helper class (`app/Helpers/DB.php`) provides a static interface for all database interactions. All queries use prepared statements for security.

## Basic Queries

### SELECT - Multiple Rows
```php
// Simple select
$clients = Db::select("SELECT * FROM clients");

// With WHERE clause
$activeClients = Db::select(
    "SELECT * FROM clients WHERE active = :active",
    [':active' => 1]
);

// With multiple conditions
$clients = Db::select(
    "SELECT * FROM clients WHERE status = :status AND created_at > :date ORDER BY name",
    [':status' => 'active', ':date' => '2024-01-01']
);

// With JOIN
$orders = Db::select(
    "SELECT o.*, c.name as client_name 
     FROM orders o 
     JOIN clients c ON o.client_id = c.id 
     WHERE o.status = :status",
    [':status' => 'pending']
);
```

### SELECT - Single Row
```php
// Get single row
$client = Db::getRow("SELECT * FROM clients WHERE id = :id", [':id' => $id]);

// Check if exists
if ($client) {
    echo $client['name'];
} else {
    echo "Client not found";
}

// Get specific columns
$email = Db::getRow("SELECT email FROM clients WHERE id = :id", [':id' => $id]);
```

## INSERT Operations
```php
// Simple insert
$clientId = Db::insert('clients', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '555-1234'
]);

// Insert with timestamp
$clientId = Db::insert('clients', [
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
]);

// Insert and use the ID
$orderId = Db::insert('orders', [
    'client_id' => $clientId,
    'total' => 99.99,
    'status' => 'pending'
]);
```

## UPDATE Operations
```php
// Update single record
$affected = Db::update('clients',
    ['name' => 'John Smith', 'updated_at' => date('Y-m-d H:i:s')],
    'id = :id',
    [':id' => $id]
);

// Update multiple records
$affected = Db::update('orders',
    ['status' => 'shipped', 'shipped_at' => date('Y-m-d H:i:s')],
    'status = :old_status AND created_at < :date',
    [':old_status' => 'pending', ':date' => '2024-01-01']
);

// Increment value
Db::execute(
    "UPDATE products SET view_count = view_count + 1 WHERE id = :id",
    [':id' => $productId]
);
```

## DELETE Operations
```php
// Delete single record
$affected = Db::delete('clients', 'id = :id', [':id' => $id]);

// Delete multiple records
$affected = Db::delete('logs', 'created_at < :date', [':date' => '2024-01-01']);

// Soft delete (recommended)
Db::update('clients',
    ['deleted_at' => date('Y-m-d H:i:s')],
    'id = :id',
    [':id' => $id]
);
```

## Transactions

Use transactions for operations that must all succeed or all fail together.
```php
// Simple transaction
Db::beginTransaction();
try {
    // Create order
    $orderId = Db::insert('orders', [
        'client_id' => $clientId,
        'total' => 100.00,
        'status' => 'pending'
    ]);
    
    // Deduct from client balance
    Db::execute(
        "UPDATE clients SET balance = balance - :amount WHERE id = :id",
        [':amount' => 100.00, ':id' => $clientId]
    );
    
    // Insert order items
    foreach ($items as $item) {
        Db::insert('order_items', [
            'order_id' => $orderId,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'price' => $item['price']
        ]);
    }
    
    Db::commit();
    echo "Order created successfully";
    
} catch (Exception $e) {
    Db::rollback();
    echo "Order failed: " . $e->getMessage();
}
```

## Complex Queries

### Aggregation
```php
// COUNT
$count = Db::getRow("SELECT COUNT(*) as total FROM clients WHERE active = 1");
echo $count['total'];

// SUM, AVG, etc.
$stats = Db::getRow("
    SELECT 
        COUNT(*) as order_count,
        SUM(total) as revenue,
        AVG(total) as avg_order
    FROM orders 
    WHERE status = 'completed'
");
```

### Subqueries
```php
$clients = Db::select("
    SELECT c.*, 
           (SELECT COUNT(*) FROM orders WHERE client_id = c.id) as order_count,
           (SELECT SUM(total) FROM orders WHERE client_id = c.id) as total_spent
    FROM clients c
    WHERE c.active = 1
");
```

### GROUP BY
```php
$monthlySales = Db::select("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as order_count,
        SUM(total) as revenue
    FROM orders
    WHERE status = 'completed'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
");
```

### CASE Statements
```php
$clients = Db::select("
    SELECT 
        name,
        CASE 
            WHEN total_spent > 10000 THEN 'VIP'
            WHEN total_spent > 5000 THEN 'Premium'
            ELSE 'Standard'
        END as tier
    FROM clients
");
```

## Raw Queries with execute()

For queries that don't fit insert/update/delete patterns:
```php
// Custom UPDATE with calculations
Db::execute("
    UPDATE products 
    SET discount_price = price * 0.9 
    WHERE category = :category",
    [':category' => 'electronics']
);

// Batch operations
Db::execute("
    INSERT INTO archive_clients 
    SELECT * FROM clients 
    WHERE deleted_at < :date",
    [':date' => date('Y-m-d', strtotime('-1 year'))]
);
```

## Performance Tips

### Use LIMIT for Large Datasets
```php
// Paginated results
$page = $_GET['page'] ?? 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$clients = Db::select(
    "SELECT * FROM clients LIMIT :limit OFFSET :offset",
    [':limit' => $perPage, ':offset' => $offset]
);
```

### Index Your Queries
```sql
-- Add indexes for frequently queried columns
CREATE INDEX idx_clients_email ON clients(email);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_client_id ON orders(client_id);
```

### Use EXISTS Instead of COUNT
```php
// Slow
$exists = Db::getRow("SELECT COUNT(*) as c FROM clients WHERE email = :email", [':email' => $email]);
if ($exists['c'] > 0) { }

// Fast
$exists = Db::getRow("SELECT 1 FROM clients WHERE email = :email LIMIT 1", [':email' => $email]);
if ($exists) { }
```

# Common Real-World Scenarios

## File Upload Handling
```php
<?php
class Documents extends Controller
{
    public function upload()
    {
        try {
            if (!isset($_FILES['document'])) {
                throw new Exception('No file uploaded');
            }
            
            $file = $_FILES['document'];
            
            // Validate file
            $maxSize = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $maxSize) {
                throw new Exception('File too large (max 5MB)');
            }
            
            $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Invalid file type');
            }
            
            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $ext;
            $uploadPath = UPLOADS_PATH . '/documents/' . $filename;
            
            // Move file
            if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                throw new Exception('Failed to save file');
            }
            
            // Save to database
            $docId = Db::insert('documents', [
                'user_id' => Auth::getUserId(),
                'filename' => $filename,
                'original_name' => $file['name'],
                'file_size' => $file['size'],
                'mime_type' => $file['type'],
                'uploaded_at' => date('Y-m-d H:i:s')
            ]);
            
            redirect('/documents/show/' . $docId);
            
        } catch (Exception $e) {
            $this->view->data['error'] = $e->getMessage();
            $this->view->render('documents/upload', true, 'site');
        }
    }
}
```

## Pagination
```php
<?php
class Products extends Controller
{
    public function index()
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $total = Db::getRow("SELECT COUNT(*) as count FROM products");
        $totalPages = ceil($total['count'] / $perPage);
        
        // Get paginated results
        $products = Db::select(
            "SELECT * FROM products ORDER BY created_at DESC LIMIT :limit OFFSET :offset",
            [':limit' => $perPage, ':offset' => $offset]
        );
        
        $this->view->data['products'] = $products;
        $this->view->data['current_page'] = $page;
        $this->view->data['total_pages'] = $totalPages;
        $this->view->render('products/index', true, 'site');
    }
}
```

## Search with Filters
```php
<?php
class Clients extends Controller
{
    public function search()
    {
        $query = "SELECT * FROM clients WHERE 1=1";
        $params = [];
        
        // Search term
        if (!empty($_GET['q'])) {
            $query .= " AND (name LIKE :search OR email LIKE :search)";
            $params[':search'] = '%' . $_GET['q'] . '%';
        }
        
        // Status filter
        if (isset($_GET['status']) && $_GET['status'] !== '') {
            $query .= " AND status = :status";
            $params[':status'] = $_GET['status'];
        }
        
        // Date range
        if (!empty($_GET['date_from'])) {
            $query .= " AND created_at >= :date_from";
            $params[':date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $query .= " AND created_at <= :date_to";
            $params[':date_to'] = $_GET['date_to'] . ' 23:59:59';
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $clients = Db::select($query, $params);
        
        $this->view->data['clients'] = $clients;
        $this->view->data['filters'] = $_GET;
        $this->view->render('clients/search', true, 'site');
    }
}
```

## Authentication System
```php
<?php
// app/Helpers/Auth.php
class Auth
{
    public static function login($email, $password)
    {
        $user = Db::getRow("SELECT * FROM users WHERE email = :email", [':email' => $email]);
        
        if (!$user || !password_verify($password, $user['password'])) {
            throw new Exception('Invalid credentials');
        }
        
        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_role', $user['role']);
        
        // Update last login
        Db::update('users', 
            ['last_login' => date('Y-m-d H:i:s')],
            'id = :id',
            [':id' => $user['id']]
        );
        
        return true;
    }
    
    public static function logout()
    {
        Session::destroy();
    }
    
    public static function isLoggedIn()
    {
        return Session::has('user_id');
    }
    
    public static function getUserId()
    {
        return Session::get('user_id');
    }
    
    public static function getUser()
    {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return Db::getRow("SELECT * FROM users WHERE id = :id", [':id' => self::getUserId()]);
    }
    
    public static function requireRole($role)
    {
        if (Session::get('user_role') !== $role) {
            throw new Exception('Unauthorized');
        }
    }
}

// Login controller
class Login extends Controller
{
    public function index()
    {
        if (Auth::isLoggedIn()) {
            redirect('/dashboard');
        }
        
        $this->view->render('auth/login', false);
    }
    
    public function submit()
    {
        try {
            Auth::login($_POST['email'], $_POST['password']);
            redirect('/dashboard');
        } catch (Exception $e) {
            $this->view->data['error'] = $e->getMessage();
            $this->view->render('auth/login', false);
        }
    }
    
    public function logout()
    {
        Auth::logout();
        redirect('/login');
    }
}
```
## Modal Integration System
Working with Modals; I have a dynamic modal intergration system.
data-url is the link to the modal 
Modal Views are located under  public/views/modals/(modal_name.php)

## Modal Structure
```
/www/wwwroot/(project_directory)/
├── public/
    ├── views/
        └── modals/
```

```html 
<button class="RegularModal btn fw-bold btn-primary" data-url="/modals/load/addorg" data-size="mw-650px">New Company</button>
```

### Building the Modal
```html 

<!--begin::Modal dialog-->
<!--begin::Modal content-->
<div class="modal-content">
    <!--begin::Modal header-->
    <div class="modal-header" id="(UNIQUE_ID)">
        <!--begin::Modal title-->
        <h2>(Modal Title Name Here)</h2>
        <!--end::Modal title-->
        <!--begin::Close-->
        <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
            <i class="ki-duotone ki-cross fs-1">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </div>
        <!--end::Close-->
    </div>
    <!--end::Modal header-->
    (MODAL CONTENT HERE)
</div>
<!--end::Modal content-->
<!--end::Modal dialog-->
```

<?php
require_once dirname(__DIR__) . '/app/config/config.php';

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Setting up organization system tables...\n";

try {
    // Step 1: Create tables
    echo "Step 1: Creating organization tables...\n";
    $tablesFile = dirname(__DIR__) . '/sql/organizations_tables.sql';
    
    if (!file_exists($tablesFile)) {
        throw new Exception("Tables file not found: {$tablesFile}");
    }
    
    $tablesSchema = file_get_contents($tablesFile);
    
    if ($tablesSchema === false) {
        throw new Exception("Could not read tables file");
    }
    
    // Execute the tables schema
    if ($conn->multi_query($tablesSchema)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
            if ($conn->more_results()) {
                echo "✓ Created table\n";
            }
        } while ($conn->next_result());
        
        echo "✓ All tables created successfully\n";
    } else {
        echo "✗ Error creating tables: " . $conn->error . "\n";
        throw new Exception("Failed to create tables: " . $conn->error);
    }
    
    // Step 2: Create triggers
    echo "Step 2: Creating organization triggers...\n";
    $triggersFile = dirname(__DIR__) . '/sql/organizations_triggers.sql';
    
    if (!file_exists($triggersFile)) {
        throw new Exception("Triggers file not found: {$triggersFile}");
    }
    
    $triggersSchema = file_get_contents($triggersFile);
    
    if ($triggersSchema === false) {
        throw new Exception("Could not read triggers file");
    }
    
    // Execute the triggers schema
    if ($conn->multi_query($triggersSchema)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
            if ($conn->more_results()) {
                echo "✓ Created trigger\n";
            }
        } while ($conn->next_result());
        
        echo "✓ All triggers created successfully\n";
    } else {
        echo "✗ Error creating triggers: " . $conn->error . "\n";
        throw new Exception("Failed to create triggers: " . $conn->error);
    }
    
    // Create storage directories
    $storageRoot = dirname(__DIR__) . '/storage/';
    $organizationsDir = $storageRoot . 'organizations/';
    
    if (!is_dir($organizationsDir)) {
        if (mkdir($organizationsDir, 0755, true)) {
            echo "✓ Created organizations storage directory\n";
        } else {
            echo "✗ Failed to create organizations storage directory\n";
        }
    } else {
        echo "✓ Organizations storage directory already exists\n";
    }
    
    echo "\n✅ Organization system setup completed successfully!\n\n";
    
    echo "=== Next Steps ===\n";
    echo "1. Create view templates in app/views/organizations/\n";
    echo "2. Add organization routes to your routing system\n";
    echo "3. Add organization navigation to your dashboard\n";
    echo "4. Test the organization creation and management features\n\n";
    
    echo "=== Key Features Available ===\n";
    echo "- Organization creation and management\n";
    echo "- Role-based member management (owner, admin, executive, member, treasurer)\n";
    echo "- Invitation system with email tokens\n";
    echo "- Financial transaction tracking\n";
    echo "- Event-organization linking\n";
    echo "- File-based extended data storage\n\n";
    
    echo "=== Database Tables Created ===\n";
    echo "- organizations (main organization data)\n";
    echo "- organization_members (membership and roles)\n";
    echo "- organization_invitations (invitation system)\n";
    echo "- organization_finances (financial accounts)\n";
    echo "- organization_transactions (financial transactions)\n";
    echo "- events table updated with org_id and payment fields\n\n";

} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
} finally {
    $conn->close();
}

echo "Organization setup script finished.\n";
?> 
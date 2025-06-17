
<?php
// git-pull.php

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Run the git pull command
    $output = [];
    $result_code = null;

    // Run git pull in the current directory
    exec('git pull 2>&1', $output, $result_code);

    // Store the result in a session variable so it can be displayed after redirect
    session_start();
    $_SESSION['git_pull_output'] = $output;
    $_SESSION['git_pull_result_code'] = $result_code;

    // Redirect to the same page to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Start a session to retrieve the result after redirect
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Git Pull Button</title>
</head>
<body>
    <!-- Form to trigger git pull -->
    <form method="POST">
        <button type="submit">Run Git Pull</button>
    </form>

    <?php
    // If there was output from the git pull, display it
    if (isset($_SESSION['git_pull_output'])) {
        $output = $_SESSION['git_pull_output'];
        $result_code = $_SESSION['git_pull_result_code'];

        if ($result_code === 0) {
            echo "<pre>Git pull successful:\n" . implode("\n", $output) . "</pre>";
        } else {
            echo "<pre>Git pull failed with code $result_code:\n" . implode("\n", $output) . "</pre>";
        }

        // Clear session data after displaying the result
        unset($_SESSION['git_pull_output']);
        unset($_SESSION['git_pull_result_code']);
    }
    ?>
</body>
</html>


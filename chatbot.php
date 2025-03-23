<?php
// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_password = '';
$db_name = 'chatbot_db';

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if (!$conn->query($sql)) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($db_name);

// Create chats table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS chats (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    query VARCHAR(255) NOT NULL,
    response TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die("Error creating table: " . $conn->error);
}

// Serper API configuration
$serper_api_key = "c098b6a72884590a0e9895b36502b52b7d989664";
$serper_api_url = "https://google.serper.dev/search";

/**
 * Perform a search using the Serper API
 * @param string $query The search query
 * @return array The search results
 */
function search_with_serper($query, $api_key, $api_url) {
    $headers = [
        'X-API-KEY: ' . $api_key,
        'Content-Type: application/json'
    ];
    
    $payload = json_encode([
        'q' => $query,
        'num' => 5 // Number of results to return
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code != 200) {
        return ["error" => "API request failed with status code $http_code"];
    }
    
    return json_decode($response, true);
}

/**
 * Generate a chatbot response based on search results
 * @param array $search_results The search results from Serper API
 * @return string The formatted response
 */
function generate_response($search_results) {
    if (isset($search_results["error"])) {
        return "Sorry, I encountered an error: " . $search_results["error"];
    }
    
    if (!isset($search_results["organic"]) || empty($search_results["organic"])) {
        return "I couldn't find any relevant information for your query.";
    }
    
    // Extract information from organic search results
    $results = $search_results["organic"];
    $response = "Here's what I found:\n\n";
    
    $count = min(count($results), 3); // Limit to top 3 results
    for ($i = 0; $i < $count; $i++) {
        $result = $results[$i];
        $title = isset($result["title"]) ? $result["title"] : "No title";
        $snippet = isset($result["snippet"]) ? $result["snippet"] : "No description available";
        $link = isset($result["link"]) ? $result["link"] : "#";
        
        $response .= ($i + 1) . ". **" . $title . "**\n" . $snippet . "\n[Source](" . $link . ")\n\n";
    }
    
    $response .= "Is there anything specific from these results you'd like to know more about?";
    return $response;
}

/**
 * Save the chat to the database
 * @param mysqli $conn Database connection
 * @param string $query User query
 * @param string $response Bot response
 * @return bool True if saved successfully, false otherwise
 */
function save_chat_to_db($conn, $query, $response) {
    $stmt = $conn->prepare("INSERT INTO chats (query, response, timestamp) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $query, $response);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// Handle API requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'chat') {
    header('Content-Type: application/json');
    
    // Get JSON data from the request
    $data = json_decode(file_get_contents('php://input'), true);
    $query = isset($data['query']) ? $data['query'] : '';
    
    if (empty($query)) {
        echo json_encode(["response" => "Please ask a question."]);
        exit;
    }
    
    // Get search results
    $search_results = search_with_serper($query, $serper_api_key, $serper_api_url);
    
    // Generate response based on search results
    $response = generate_response($search_results);
    
    // Save the chat to the database
    save_chat_to_db($conn, $query, $response);
    
    echo json_encode(["response" => $response]);
    exit;
}

// Close database connection - important for main page to avoid connection issues
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Serper API Chatbot</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .chat-container { border: 1px solid #ccc; border-radius: 5px; padding: 10px; height: 400px; overflow-y: auto; margin-bottom: 10px; }
        .input-container { display: flex; }
        #user-input { flex-grow: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-right: 10px; }
        button { padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .message { margin-bottom: 10px; padding: 8px; border-radius: 5px; }
        .user-message { background-color: #e3f2fd; text-align: right; }
        .bot-message { background-color: #f1f1f1; }
        .history-container { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Serper API Chatbot</h1>
    <div class="chat-container" id="chat-container"></div>
    <div class="input-container">
        <input type="text" id="user-input" placeholder="Ask something...">
        <button onclick="sendMessage()">Send</button>
    </div>
    
    <div class="history-container">
        <h2>Recent Chats</h2>
        <table id="chat-history">
            <tr>
                <th>Time</th>
                <th>Query</th>
                <th>Response</th>
            </tr>
            <?php
            // Re-establish connection for showing history
            $conn = new mysqli($db_host, $db_user, $db_password, $db_name);
            
            if (!$conn->connect_error) {
                $result = $conn->query("SELECT * FROM chats ORDER BY timestamp DESC LIMIT 5");
                
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["timestamp"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["query"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["response"]) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No chat history yet</td></tr>";
                }
                
                $conn->close();
            }
            ?>
        </table>
    </div>
    
    <script>
        function sendMessage() {
            const userInput = document.getElementById('user-input');
            const chatContainer = document.getElementById('chat-container');
            const query = userInput.value.trim();
            
            if (query === '') return;
            
            // Add user message to chat
            const userMessageDiv = document.createElement('div');
            userMessageDiv.className = 'message user-message';
            userMessageDiv.textContent = query;
            chatContainer.appendChild(userMessageDiv);
            
            // Clear input
            userInput.value = '';
            
            // Add temporary bot message
            const botMessageDiv = document.createElement('div');
            botMessageDiv.className = 'message bot-message';
            botMessageDiv.textContent = 'Thinking...';
            chatContainer.appendChild(botMessageDiv);
            
            // Scroll to bottom
            chatContainer.scrollTop = chatContainer.scrollHeight;
            
            // Send request to server
            fetch('?action=chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({query: query})
            })
            .then(response => response.json())
            .then(data => {
                // Update bot message with response
                botMessageDiv.innerHTML = data.response.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" target="_blank">$1</a>');
                
                // Add to chat history table without refreshing
                const table = document.getElementById('chat-history');
                const newRow = table.insertRow(1); // Insert after header row
                
                const timeCell = newRow.insertCell(0);
                const queryCell = newRow.insertCell(1);
                const responseCell = newRow.insertCell(2);
                
                const now = new Date();
                timeCell.innerHTML = now.toISOString().replace('T', ' ').substring(0, 19);
                queryCell.textContent = query;
                responseCell.textContent = data.response;
            })
            .catch(error => {
                botMessageDiv.textContent = 'Sorry, something went wrong. Please try again.';
                console.error('Error:', error);
            });
        }
        
        // Allow sending message with Enter key
        document.getElementById('user-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    </script>
</body>
</html>
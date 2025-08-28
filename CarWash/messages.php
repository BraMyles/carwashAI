<?php
require_once 'includes/init.php';
require_once 'includes/classes/Message.php';

if (!$auth->isLoggedIn()) {
	header('Location: ' . BASE_PATH . 'index.php');
	exit();
}

$currentUser = $auth->getCurrentUser();
$isAdmin = $auth->isAdmin();

// Determine default conversation partner:
// - If admin: first active staff
// - If staff: the admin user
$defaultOtherId = 0;
try {
	if ($isAdmin) {
		$stmt = $pdo->query("SELECT id, full_name FROM users WHERE role='staff' AND is_active=1 ORDER BY full_name LIMIT 1");
		$u = $stmt->fetch(PDO::FETCH_ASSOC);
		$defaultOtherId = $u ? intval($u['id']) : 0;
	} else {
		$stmt = $pdo->query("SELECT id, full_name FROM users WHERE role='admin' AND is_active=1 ORDER BY id LIMIT 1");
		$u = $stmt->fetch(PDO::FETCH_ASSOC);
		$defaultOtherId = $u ? intval($u['id']) : 0;
	}
} catch(Exception $e) {
	error_log("Messages.php error getting default user: " . $e->getMessage());
}

$selectedOtherId = intval($_GET['other_id'] ?? ($defaultOtherId ?: 0));

// Build list of possible recipients
$recipients = [];
try {
	if ($isAdmin) {
		$stmt = $pdo->query("SELECT id, full_name FROM users WHERE role='staff' AND is_active=1 ORDER BY full_name");
		$recipients = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
	} else {
		$stmt = $pdo->query("SELECT id, full_name FROM users WHERE role='admin' AND is_active=1 ORDER BY full_name");
		$recipients = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
	}
} catch(Exception $e) {
	error_log("Messages.php error getting recipients: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Messages - Car Wash</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?php echo BASE_PATH; ?>assets/css/ai-modern-theme.css" rel="stylesheet">
</head>
<body>
	<div class="floating-elements"><div class="floating-element"></div><div class="floating-element"></div><div class="floating-element"></div></div>
	<nav class="navbar navbar-expand-lg navbar-dark">
		<div class="container-fluid">
			<a class="navbar-brand" href="<?php echo BASE_PATH; ?>dashboard.php">🚗 Car Wash AI</a>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav me-auto">
					<li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>dashboard.php">📊 Dashboard</a></li>
					<li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>customers.php">👥 Customers</a></li>
					<li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>bookings.php">📅 Bookings</a></li>
					<li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>services.php">🔧 Services</a></li>
					<li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>payments.php">💰 Payments</a></li>
					<?php if ($isAdmin): ?><li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>users.php">👤 Users</a></li><?php endif; ?>
					<li class="nav-item"><a class="nav-link active" href="<?php echo BASE_PATH; ?>messages.php">💬 Messages</a></li>
				</ul>
				<ul class="navbar-nav">
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">👤 <?php echo htmlspecialchars($currentUser['full_name']); ?></a>
						<ul class="dropdown-menu">
							<li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>logout.php">🚪 Logout</a></li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
	</nav>

	<div class="container-fluid mt-4">
		<div class="row">
			<div class="col-12 col-md-3">
				<div class="card glass-card">
					<div class="card-header"><strong>Conversations</strong></div>
					<div class="list-group list-group-flush" id="recipientList">
						<?php if (empty($recipients)): ?>
							<div class="list-group-item text-muted">No users available</div>
						<?php else: ?>
							<?php foreach ($recipients as $r): ?>
							<a class="list-group-item list-group-item-action <?php echo $selectedOtherId===$r['id']?'active':''; ?>" href="?other_id=<?php echo $r['id']; ?>">
								<?php echo htmlspecialchars($r['full_name']); ?>
							</a>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="col-12 col-md-9">
				<div class="card glass-card">
					<div class="card-header d-flex justify-content-between align-items-center">
						<strong>Chat</strong>
						<div>
							<?php if ($selectedOtherId > 0): ?>
								<span class="badge bg-secondary" id="chatPartner">
									<?php 
									$partnerName = '';
									foreach ($recipients as $r) {
										if ($r['id'] == $selectedOtherId) {
											$partnerName = $r['full_name'];
											break;
										}
									}
									echo htmlspecialchars($partnerName ?: 'User #' . $selectedOtherId);
									?>
								</span>
							<?php else: ?>
								<span class="badge bg-warning">Select a conversation</span>
							<?php endif; ?>
						</div>
					</div>
					<div class="card-body" style="height:60vh; overflow:auto;" id="chatWindow">
						<?php if ($selectedOtherId == 0): ?>
							<div class="text-center text-muted mt-5">
								<h5>Select a conversation to start messaging</h5>
								<p>Choose a user from the left panel to begin chatting.</p>
							</div>
						<?php endif; ?>
					</div>
					<div class="card-footer">
						<form id="sendForm" class="d-flex gap-2">
							<input type="hidden" id="csrf_token" value="<?php echo htmlspecialchars(getCsrfToken()); ?>">
							<input type="hidden" id="recipient_id" value="<?php echo $selectedOtherId; ?>">
							<input class="form-control" id="message_input" placeholder="Type your message..." autocomplete="off" <?php echo $selectedOtherId == 0 ? 'disabled' : ''; ?>>
							<button class="btn btn-primary" type="submit" <?php echo $selectedOtherId == 0 ? 'disabled' : ''; ?>>Send</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
	<script>
	(function(){
		const chatWindow = document.getElementById('chatWindow');
		const sendForm = document.getElementById('sendForm');
		const input = document.getElementById('message_input');
		const otherId = parseInt(document.getElementById('recipient_id').value || '0', 10);
		let lastId = 0;
		let isPolling = false;

		console.log('Messages: Initializing with otherId:', otherId);
		console.log('Messages: Current user ID:', <?php echo intval($currentUser['id']); ?>);
		console.log('Messages: CSRF token:', document.getElementById('csrf_token').value);

		function render(messages) {
			console.log('Messages: Rendering', messages.length, 'messages');
			messages.forEach(m => {
				const mine = <?php echo intval($currentUser['id']); ?> === parseInt(m.sender_id,10);
				const row = document.createElement('div');
				row.className = 'mb-2';
				row.innerHTML = `
					<div class="d-flex ${mine?'justify-content-end':'justify-content-start'}">
						<div class="p-2 rounded" style="max-width:75%; background:${mine?'rgba(79,172,254,0.2)':'rgba(255,255,255,0.1)'}; border:1px solid var(--glass-border);">
							<div class="small text-muted">${mine?'You':(m.sender_name||'User')} • ${new Date(m.created_at.replace(' ','T')).toLocaleString()}</div>
							<div>${escapeHtml(m.content)}</div>
						</div>
					</div>`;
				chatWindow.appendChild(row);
				chatWindow.scrollTop = chatWindow.scrollHeight;
			});
		}

		function escapeHtml(str){
			return str
				.replace(/&/g,'&amp;')
				.replace(/</g,'&lt;')
				.replace(/>/g,'&gt;')
				.replace(/"/g,'&quot;')
				.replace(/'/g,'&#039;');
		}

		async function poll(){
			if (!otherId || isPolling) {
				return;
			}
			
			isPolling = true;
			try {
				console.log('Messages: Polling for messages after ID:', lastId);
				const url = `<?php echo BASE_PATH; ?>api/get_messages.php?other_id=${otherId}&after_id=${lastId}`;
				console.log('Messages: Polling URL:', url);
				
				const res = await fetch(url);
				
				if (!res.ok) {
					throw new Error(`HTTP ${res.status}: ${res.statusText}`);
				}
				
				const data = await res.json();
				console.log('Messages: Poll response:', data);
				
				if (data.success && Array.isArray(data.messages)) {
					if (data.messages.length > 0) {
						console.log('Messages: Got', data.messages.length, 'new messages');
						render(data.messages);
						lastId = data.last_id || lastId;
					} else {
						console.log('Messages: No new messages');
					}
				} else {
					console.error('Messages: Invalid response format:', data);
					if (data.error) {
						console.error('Messages: API Error:', data.error);
					}
				}
			} catch(e) {
				console.error('Messages: Poll error:', e);
			} finally {
				isPolling = false;
			}
		}

		async function loadInitialMessages() {
			if (!otherId) {
				console.log('Messages: No otherId, cannot load initial messages');
				return;
			}
			console.log('Messages: Loading initial messages...');
			await poll();
		}

		sendForm && sendForm.addEventListener('submit', async function(e){
			e.preventDefault();
			const text = input.value.trim();
			if (!text || !otherId) return;
			
			console.log('Messages: Sending message:', text, 'to:', otherId);
			
			const payload = {
				recipient_id: otherId,
				content: text,
				csrf_token: document.getElementById('csrf_token').value
			};
			
			console.log('Messages: Sending payload:', payload);
			
			try {
				const url = '<?php echo BASE_PATH; ?>api/send_message.php';
				console.log('Messages: Send URL:', url);
				
				const res = await fetch(url, {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					body: JSON.stringify(payload)
				});
				
				console.log('Messages: Send response status:', res.status);
				
				if (!res.ok) {
					throw new Error(`HTTP ${res.status}: ${res.statusText}`);
				}
				
				const data = await res.json();
				console.log('Messages: Send response data:', data);
				
				if (data.success) {
					input.value = '';
					console.log('Messages: Message sent successfully, refreshing...');
					lastId = 0; // re-fetch to include our sent item with server timestamp
					await poll();
				} else {
					console.error('Messages: Failed to send message:', data.error);
					alert('Failed to send message: ' + (data.error || 'Unknown error'));
				}
			} catch(e) {
				console.error('Messages: Send error:', e);
				alert('Error sending message: ' + e.message);
			}
		});

		// Load initial messages when page loads
		if (otherId > 0) {
			loadInitialMessages();
		}

		// Start polling
		const pollInterval = setInterval(() => {
			if (otherId > 0) {
				poll();
			}
		}, 3000);
		
		console.log('Messages: System initialized, otherId:', otherId);
	})();
	</script>
</body>
</html>



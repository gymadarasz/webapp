<h1>Main</h1>
<?php echo isset($this) ? $this->create('messages.html.php', $this->data) : ''; ?>
<a href="?q=logout">Logout</a>

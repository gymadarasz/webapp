<h1>Error</h1>
<?php echo isset($this) ? $this->create('messages.html.php', $this->data) : ''; ?>
<a href="<?php echo $base ?? ''; ?>">Back</a>

<h1>Registry</h1>
<?php echo isset($this) ? $this->create('messages.html.php', $this->data) : ''; ?>
<form method="POST">
    <input type="email" name="email" placeholder="Email" value="<?php echo $email ?? ''; ?>">
    <input type="email" name="email_retype" placeholder="Retype email" value="<?php echo $emailRetype ?? ''; ?>">
    <input type="password" name="password" placeholder="Password">
    <input type="submit" value="Register">
</form>
<a href="?q=login">Login</a>

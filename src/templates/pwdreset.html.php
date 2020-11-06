<h1>Password reset</h1>
{{ isset($this) ? $this->create('messages.html.php', $this->data) : ''; }}
<form method="POST">
    <input type="email" name="email" placeholder="Email" value="{{ $email ?? ''; }}">
    <input type="submit" value="Reset password">
</form>
<a href="?q=registry">Registry</a>
<a href="?q=login">Login</a>
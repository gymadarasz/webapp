<h1>Login</h1>
{{ isset($this) ? $this->create('messages.html.php', $this->data) : ''; }}
<form method="POST" action="?q=">
    <input type="email" name="email" placeholder="Email" value="{{ $email ?? ''; }}">
    <input type="password" name="password" placeholder="Password">
    <input type="submit" value="Login">
</form>
<a href="?q=registry">Registry</a>
<a href="?q=pwdreset">Forgotten password</a>
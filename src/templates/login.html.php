<h1>Login</h1>
<?php include 'messages.html.php'; ?>
<form method="POST" action="?q=">
    <input type="email" name="email" placeholder="Email" value="<?php echo $email ?? ''; ?>">
    <input type="password" name="password" placeholder="Password">
    <input type="submit" value="Login">
</form>
<a href="?q=registry">Registry</a>
<a href="?q=pwdreset">Forgotten password</a>
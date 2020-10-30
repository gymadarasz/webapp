<h1>Password reset</h1>
<?php include 'messages.html.php'; ?>
<form method="POST">
    <input type="email" name="email" placeholder="Email" value="<?php echo $email ?? ''; ?>">
    <input type="submit" value="Reset password">
</form>
<a href="?q=registry">Registry</a>
<a href="?q=login">Login</a>
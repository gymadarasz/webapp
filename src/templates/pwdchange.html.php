<h1>Change password</h1>
<?php include 'messages.html.php'; ?>
<form method="POST">
    <input type="password" name="password" placeholder="Password">
    <input type="password" name="password_retype" placeholder="Retype password">
    <input type="submit" value="Change password">
</form>
<a href="?q=registry">Registry</a>
<a href="?q=login">Login</a>
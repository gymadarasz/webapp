<html>
    <head>
        <title>WebApp</title>
    </head>
    <body>
        <?php echo isset($this) && isset($body) ? $this->create($body, $this->data) : ''; ?>
    </body>
</html>
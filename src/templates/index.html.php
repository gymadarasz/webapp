<html>
    <head>
        <?php echo isset($this) ? $this->create('analytics.html.php', $this->data) : ''; ?>
    </head>
    <body>
        <?php echo isset($this) && isset($body) ? $this->create($body, $this->data) : ''; ?>
    </body>
</html>
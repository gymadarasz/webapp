<h1>Error</h1>
{{ isset($this) ? $this->create('messages.html.php', $this->data) : ''; }}
<a href="{{ $base ?? ''; }}">Back</a>

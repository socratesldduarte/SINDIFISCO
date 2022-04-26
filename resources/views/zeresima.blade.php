<!doctype html>
<html>
<body>
<pre>
    {!! $zeresima->content !!}
    <br>Documento emitido em {{ $zeresima->created_at->format('d/m/Y H:i:s') }}, com o código de verificação<br>{{ $zeresima->hash }}. A autenticidade pode ser verificada em<br>{{asset('/')}}documentos/, utilizando o código acima.
</pre>
</body>
</html>

<!doctype html>
<html>
<body>
<pre>
    {!! $relatorio->content !!}
    <br>Documento emitido em {{ $relatorio->created_at->format('d/m/Y H:i:s') }}, com o código de verificação<br>{{ $relatorio->hash }}. A autenticidade pode ser verificada em<br>{{asset('/')}}documentos/, utilizando o código acima.
</pre>
</body>
</html>

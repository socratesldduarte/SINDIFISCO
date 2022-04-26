<!doctype html>
<html>
<body>
<pre>
    {!! $boletimapuracao->content !!}
    <br>Documento emitido em {{ $boletimapuracao->created_at->format('d/m/Y H:i:s') }}, com o código de verificação<br>{{ $boletimapuracao->hash }}. A autenticidade pode ser verificada em<br>{{asset('/')}}documentos/, utilizando o código acima.
</pre>
</body>
</html>

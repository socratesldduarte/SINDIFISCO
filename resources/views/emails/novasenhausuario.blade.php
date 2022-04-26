<h1>SINDIFISCO-RS - Sistema de votações</h1>
<p>Olá, {{ $user->name }},<br>
Seu usuário no sistema de votações teve uma nova senha atribuída: [{{ $senha }}].</p>
<p>O acesso ao sistema pode ser feito em {{asset('/')}}eleicao/{{ $user->poll ? $user->poll->code . " " : " " }} com seu CPF [{{ $user->document }}] e a senha acima. O período de votação está disponível no site da entidade.</p>
<p>Qualquer dúvida, queira entrar em contato com a Secretaria do SINDIFISCO-RS.</p>
<hr>
E-mail enviado em {{ date('d/m/Y H:i:s') }}

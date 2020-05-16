<h1>AFISVEC - Sistema de votações</h1>
<p>Olá, {{ $user->name }},<br>
Seu usuário no sistema de votações foi liberado para acesso por 5 minutos, a partir de um comando da Comissão Eleitoral. Se o sr. não solicitou essa liberação, pedimos que apresente esse e-mail à AFISVEC.</p>
<p>O acesso ao sistema pode ser feito em https://afisvec.org.br/eleicoes/ com seu CPF [{{ $user->document }}] e sem utilização de senha (dentro do limite especificado de tempo). O período de votação está disponível no site da entidade.</p>
<p>Qualquer dúvida, queira entrar em contato com a Secretaria da AFISVEC.</p>
<hr>
E-mail enviado em {{ date('d/m/Y H:i:s') }}

<p>Je bent door {{ $invitedBy }} uitgenodigd om jouw mening te delen 
in de groep "{{ $selectGroupName }}" op Yixow.</p>
 
<p>Gebruik de onderstaande link <b>op je telefoon</b> als je wilt deelnemen.</p>

<p><a href="{{ url('https://api.yixow.com/api/invites/private/' . $hashGroupId) }}">Ja, ik wil deelnemen.</a></p>

Hartelijke groet,<br />
Yixow, open in opinie.
<html>
<head>
</head>
<body style='font-family: Helvetica, Arial, sans-serif; font-size: 10pt; margin:auto; width: 90%'>

Je hebt jezelf een vraag van Yixow per email toegestuurd om daarop een antwoord te kunnen geven.<br />

<br />
<!-- <img width=302px src="https://www.yixow.com/api/api/images/ffaeea95dfd15db3c15e710efeb71d5b.png"> -->
@if (!isset($qImage))
	<img width=302px src="https://yixow.com/images/placeholder.png">
@else
	<img width=302px src="{{$qImage}}">
@endif
<br />
<br />
<table><tr><td style='width: 290px;font-family: Helvetica, Arial, sans-serif; font-size: 10pt'>
{{$questionText}}
</td></tr></table>
<br />
<!-- button for link that works everywhere; see https://litmus.com/blog/a-guide-to-bulletproof-buttons-in-email-design -->
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>
      <table border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td style='width: 302px'>
             <a href={{$linkUrl}} target="_blank" style="width: 300px; text-align: center; font-size: 15px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; border-radius: 3px; -webkit-border-radius: 3px; -moz-border-radius: 3px; background-color: #5C5E5C; border-top: 12px solid #5C5E5C; border-bottom: 12px solid #5C5E5C; border-right: 1px solid #5C5E5C; border-left: 1px solid #5C5E5C; display: inline-block;">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Antwoorden&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			 </a>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
<br /><br />

</body></html>
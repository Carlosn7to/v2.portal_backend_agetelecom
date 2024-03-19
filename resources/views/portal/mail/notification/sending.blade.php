<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Black November</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>


<body style="font-family: 'Montserrat', sans-serif; margin: 0; padding: 0; color: #41444A;">
<table width="600" cellspacing="0" cellpadding="0" bgcolor="#E1E1E1" style="margin: 0 auto; padding: 40px;">
    <tr>
        <td>
            <table width="100%" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" style="border-radius: 20px;">
                <tr id="header">
                    <td
                        style="background: url('https://agenotifica.s3.sa-east-1.amazonaws.com/age/Group+415.svg') no-repeat center; background-size: cover; padding: 0; margin: 0;">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" height="350px">
                            <tr>
                                <td align="center" valign="middle" style="padding: 0; margin: 0;">
                                    <p style="margin: 0; color: #ffffff; font-size: 16px; font-weight: bolder"><i>{{$data['header']['title']}}</i></p>
                                    <p style="margin: 0; color: #ffffff; font-size: 32px;">{{ $data['header']['subTitle']  }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td align="center">
                        <table width="600">
                            <tr>
                                <td
                                    style="font-size: 56px; padding: 20px 40px 10px 40px; text-align: left; line-height: 2; background: #FFFFFF; border-radius: 25px;">
                                    <p style="font-size: 24px; color: #373737;"><strong
                                            style="color:#373737 ;">Prezado(a), </strong></p>
                                    <p style="font-size: 12px; color: #373737; text-align: left;">
                                        Data: {{ \Carbon\Carbon::now()->format('d/m/Y') }}
                                    </p>
                                    <p style="font-size: 20px; color: #373737;">{{ $data['messageMail'] }}
                                    </p>
                                    @if($data['tableVisible'])
                                        <table class="table" style="width: 100%; border-collapse: collapse;">

                                            <style>
                                                .table th {
                                                    background-color: #ececec;
                                                }

                                                .table tr:nth-child(odd) {
                                                    background-color: #F1F1F1;
                                                }

                                                .table tr:nth-child(even) {
                                                    background-color: #fafafa;
                                                }
                                            </style>

                                            <tr>
                                                @foreach($data['table']['titles'] as $title)
                                                    <th style="font-size: 16px; padding: 8px; text-align: left; max-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                        {{ $title }}
                                                    </th>
                                                @endforeach
                                            </tr>
                                            @foreach($data['table']['data'] as $row)
                                                <tr>
                                                    @foreach($row as $value)
                                                        <td style="font-size: 16px; padding: 8px; text-align: left; max-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                            {{ $value }}
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </table>
                                    @endif


                                    <p style="font-size: 16px; color: #C1C1C1; text-align: left;"><strong>Este é um
                                            e-mail automático</strong>
                                    </p>
                                    <p style="font-size: 14px; color: #373737;"><strong style="color:#373737 ;"><a
                                                style="color: inherit; cursor: none" href="#">Equipe de Desenvolvimento Age
                                                Telecom</a></strong></p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table width="600" cellspacing="0" cellpadding="0" bgcolor="#2A4CC2"
                   style="border-radius: 20px; margin-top: 30px; min-width: 90%; margin-bottom: 20px;">
                <tr>
                    <td align="center">
                        <img src="https://ageavisos.s3.sa-east-1.amazonaws.com/ReguaAvisos/logoAgeTelecom.png"
                             alt="Descrição da Imagem" style="max-width: 200px; padding: 25px;">
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</td>
</tr>
</table>
</body>

</html>

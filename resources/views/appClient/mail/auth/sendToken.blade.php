<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Pós Compra</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>

<body style="font-family: 'Montserrat', sans-serif; margin: 0; padding: 0;">
    <table width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
        <tr>
            <td align="center">
                <table width="600" cellspacing="0" cellpadding="0" bgcolor="#FFFDF9">
                    <tr>
                        <td style="padding: 0 0 0 0; text-align: center; width: 100%;">
                            <img style="height: 400px;"
                                src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/notificasetembro/assets/Boas+vindas.png"
                                alt="">
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 20px 0 0 0">
                            <table width="550">
                                <tr>
                                    <td
                                        style="font-size: 17px; padding: 0 40px 10px 40px; text-align: center; line-height: 1.5;background: #ffffff; border: 1px solid #ffffff; border-radius: 25px; ">
                                        <p style="font-size: 30px">Seja bem-vindo à <br><strong
                                                style="color: #ec681c;">Age
                                                Telecom!</strong></p>

                                        <p>Olá, {{ mb_convert_case($data['name'], MB_CASE_TITLE, 'utf8') }}</p>
                                        <p>Insira o código de 6 dígitos abaixo para confirmar sua identidade e ter
                                            acesso à sua conta no aplicativo age.
                                        </p>
                                        <span><strong>{{ $data['token'] }}</strong></span>
                                        <p>Obrigado por nos ajudar a manter sua conta segura.
                                        </p>

                                        <p>Atenciosamente,<br><b style="color: #ec681c;">Time Age Telecom</b></p>
                                    </td>
                                </tr>
                            </table>
                            <table>
                                <tr>
                                    <td>

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            <table width="400">
                                <tr>
                                    <td style="text-align: center; width: 25%;">
                                        <a href="https://portal.agetelecom.com.br" target="_blank">
                                            <img style="width: 50%;"
                                                src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/notificasetembro/assets/Grupo+55%402x.png"
                                                alt="">
                                        </a>
                                        <p style="font-size: 12px">Portal</p>
                                    </td>
                                    <td style="text-align: center; width: 25%;">
                                        <a href="https://api.whatsapp.com/send?phone=556140404040&text=Ol%C3%A1%21+Gostaria+de+contratar+os+planos+de+internet+fibra+da+Age"
                                            target="_blank">
                                            <img style="width: 50%;"
                                                src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/notificasetembro/assets/Grupo+54%402x.png"
                                                alt="">
                                        </a>
                                        <p style="font-size: 12px">WhatsApp</p>
                                    </td>
                                    <td style="text-align: center; width: 25%;">
                                        <a href="https://www.instagram.com/agetelecom/" target="_blank">
                                            <img style="width: 50%;"
                                                src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/notificasetembro/assets/Grupo+53%402x.png"
                                                alt="">
                                        </a>
                                        <p style="font-size: 12px;">Instagram</p>
                                    </td>
                                    <td style="text-align: center; width: 25%;">
                                        <a href="https://me-qr.com/pt/link-list/ROPaYFpr/show" target="_blank">
                                            <img style="width: 50%;"
                                                src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/notificasetembro/assets/Grupo+52%402x.png"
                                                alt="">
                                        </a>
                                        <p style="font-size: 12px">APP</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            <table width="400">
                                <tr>
                                    <td style="border-top: 1px solid #9CA0A8; text-align: center; font-size: 12px;">
                                        <p>Canais de Atendimento ao cliente:</p>
                                        <p>www.portal.agetelecom.com.br</p>
                                        <p>WhatsApp: (61) 4040-4040</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; font-size: 12px;">
                                        <p>SIA Trecho 17 - Guará, Brasília - DF, 71200-228</p>
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

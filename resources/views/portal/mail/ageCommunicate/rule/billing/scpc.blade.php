<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SCPC</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
@php
    use Carbon\Carbon;

    $date = Carbon::now();
    $carbonDate = Carbon::parse($date);

    $dia = $carbonDate->day;
    $mes = $carbonDate->format('m');
    $ano = $carbonDate->year;

    switch ($mes) {
        case 1:
            $mes = 'Janeiro';
            break;
        case 2:
            $mes = 'Fevereiro';
            break;
        case 3:
            $mes = 'Março';
            break;
        case 4:
            $mes = 'Abril';
            break;
        case 5:
            $mes = 'Maio';
            break;
        case 6:
            $mes = 'Junho';
            break;
        case 7:
            $mes = 'Julho';
            break;
        case 8:
            $mes = 'Agosto';
            break;
        case 9:
            $mes = 'Setembro';
            break;
        case 10:
            $mes = 'Outubro';
            break;
        case 11:
            $mes = 'Novembro';
            break;
        case 12:
            $mes = 'Dezembro';
            break;
    }

    $mes = ucfirst(mb_strtolower($mes, 'UTF-8')); // Converte a primeira letra do mês para maiúscula
    $dateFormatted = "$dia de $mes de $ano";
@endphp
<table width="100%" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
    <tr>
        <td align="center">
            <table width="600" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="padding: 20px 0; text-align: center;">
                        <img src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/scpc/header-logo.png" alt="">
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px; text-align: left;">
                        <p>Brasília, {{$dateFormatted}}</p>
                        <p><strong>{{$client['name']}}</strong></p>
                        <p><strong>CPF:{{$client['tx_id']}}</strong></p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px; text-align: justify;">
                        <p>Conforme art. 43, § 2º, do Código de Defesa do Consumidor, comunicamos a abertura de cadastro
                            para o seu nome, onde os credores poderão registrar as obrigações de sua responsabilidade.
                            Tendo em vista que foi averiguado atraso de mais de 30 dias sobre faturas vencidas em seu
                            nome, a AGE TELECOMUNICAÇÕES solicitou a inclusão do(s) seguinte(s) débito(s) em seu nome
                            nas bases de dados dos serviços de proteção ao crédito Serasa Experian, Boa Vista SCPC e
                            Quod:</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px; text-align: left;">
                        <p><strong>CNPJ do Credor:</strong> 40.085.642/0001-55</p>
                        <p><strong>Endereço do Credor:</strong> ST SIA TRECHO 17 VIA IA 4 LT 1080</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 5px 0; text-align: center; font-size: 10px">
                        <h1>Dados do Débito(s)</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px; text-align: left;">
                        <table width="100%" cellspacing="0" cellpadding="0" border="1"
                               style="border-collapse: collapse;">
                            <tr>
                                <th style="padding: 5px; background-color: #ffffff; color: #000000; font-size: 13px;">
                                    CONTRATO
                                </th>
                                <th style="padding: 5px; background-color: #ffffff; color: #000000;font-size: 13px;">
                                    NATUREZA DA
                                    OPERAÇÃO
                                </th>
                                <th style="padding: 5px; background-color: #ffffff; color: #000000;font-size: 13px;">
                                    VALOR ORIGINAL DO DÉBITO
                                </th>
                                <th style="padding: 5px; background-color: #ffffff; color: #000000;font-size: 13px;">
                                    DATA DO DÉBITO
                                </th>
                            </tr>
                            <tr>
                                <td style="padding: 10px;">{{$client['contract_id']}}</td>
                                <td style="padding: 10px;">Mensalidade AGE</td>
                                <td style="padding: 10px;">R$ {{$client['document_amount']}}</td>
                                <td style="padding: 10px;">{{$client['expiration_date']}}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 20px 0; text-align: justify;">
                        <p>Você tem o prazo de 10 dias a contar da data de emissão desta notificação para regularizar
                            o(s) débito(s). Após esse prazo, não havendo quitação do débito pelo devedor ou manifestação
                            pelo credor, a(s) informação(ões) será(ão) disponibilizada(s) para consulta no(s) banco(s)
                            de dados de proteção ao crédito.</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; text-align: justify;">
                        <p>Destacamos que a quitação do(s) débito(s) aqui indicado(s) não satisfaz a quitação de
                            eventuais outros débitos em aberto em seu nome que porventura não tenham atingido 30 dias de
                            inadimplência.</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 0; text-align: justify;">
                        <p>Caso tenha dúvidas acerca do débito ou precise de auxílio adicional, nossa equipe de
                            atendimento está à disposição para ajudá-lo(a), por meios dos seguintes canais de
                            comunicação:</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 20px 0; text-align: justify; color: red; font-weight: 800;">
                        <p>Caso já tenha efetuado o pagamento, favor desconsiderar este comunicado.</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 20px 0; text-align: left;">
                        <p><strong>AGE TELECOM</strong></p>
                        <p><strong>Site:</strong> www.agetelecom.com.br</p>
                        <p><strong>Central de Relacionamento:</strong> (61) 4040-4040</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 20px 0; text-align: left;">
                        <p><strong>AGE TELECOM</strong></p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 20px 0; text-align: center;">
                        <a href="https://linktree.com/agetelecom"><img
                                src="https://agenotifica.s3.sa-east-1.amazonaws.com/age/scpc/footer-logo.png"
                                alt="Footer Image" class="imgFooter"></a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

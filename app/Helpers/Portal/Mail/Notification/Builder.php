<?php

namespace App\Helpers\Portal\Mail\Notification;

use App\Mail\Portal\Helpers\Notification\SendNotification;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport; // Importe a classe ReportExport adequada

class Builder
{
    private array $headers; // Cabeçalhos das colunas do arquivo Excel



    //  Padrão a ser seguido para a variável $dataView
    //        $dataView = [
    //            'header' => [
    //                'title' => '', // Título da imagem do e-mail
    //                'subTitle' => '' // Subtítulo da imagem do e-mail
    //            ],
    //            'messageMail' => '', // Mensagem do corpo do e-mail
    //
    //            // Tabela de informações do e-mail, caso não seja necessário mantenha a TableVisible como false
    //            'table' => [
    //                'titles' => [], // Separar por vírgula os títulos das colunas
    //                'data' => [[]],
    //            ],
    //            'tableVisible' => false
    //        ];
    private array $dataView; // Dados da view


    private string $filePath; // Caminho do arquivo Excel
    private string $storagePath; // Caminho do diretório de armazenamento
    private string $fileName; // Nome do arquivo Excel
    private array $dataReport; // Dados do relatório Excel
    private array $recipients; // Destinatários do e-mail


    // Existe uma prioziração no título do e-mail e abaixo está a explicação para elas.
    // Emergência: Para situações críticas que exigem ação imediata para evitar danos graves ou perdas significativas.
    // Alerta: Para comunicar eventos importantes que requerem atenção imediata, mas não representam uma emergência direta.
    // Aviso: Para informar sobre condições ou eventos que não são urgentes, mas ainda precisam ser observados ou tratados.
    // Informativo: Para compartilhar atualizações, relatórios ou insights que são úteis, mas não exigem ação imediata.
    // Notificação: Para comunicar eventos ou atividades rotineiras que não exigem ação, mas são relevantes para os usuários.
    private string $subject; // Assunto do e-mail

    /**
     * Construtor da classe. Invoca os métodos necessários para preparar a view, gerar o Excel e enviar o e-mail.
     *
     * @return void
     */
    public function __construct($dataView, $recipients, $subject, $dataReport = [], $fileName = '')
    {
        $this->dataView = $dataView;
        $this->dataReport = $dataReport;

        $this->subject = $subject;
        $this->fileName = $fileName;
        $this->recipients = $recipients;

        $this->viewConstruct();

        if (!empty($this->dataReport)) {
            $this->excelConstruct();
            // Remove o arquivo temporário após enviar
            unlink($this->filePath);
        }
        $this->sendMail();
    }

    /**
     * Prepara os dados da view.
     *
     * @return void
     */
    private function viewConstruct(): void
    {


    }

    /**
     * Gera o arquivo Excel.
     *
     * @return void
     */
    private function excelConstruct(): void
    {

        $this->headers = []; // Cabeçalhos das colunas do arquivo Excel
        $this->storagePath = storage_path('app/excel/');

        if (!file_exists($this->storagePath)) {
            mkdir($this->storagePath, 0777, true);
        }

        // Crie um novo arquivo Excel usando a biblioteca Maatwebsite/Excel
        $excel = Excel::download(new ReportExport($this->dataReport, $this->headers), $this->fileName.'.xlsx');
        $this->filePath = $this->storagePath . $this->fileName.'.xlsx';
        $excel->getFile()->move($this->storagePath, $this->fileName.'.xlsx');


    }

    /**
     * Envia o e-mail com o arquivo anexado.
     *
     * @return void
     */
    private function sendMail(): void
    {
        $mail = Mail::mailer('portal')->to($this->recipients)
                    ->send(new SendNotification($this->subject, $this->dataView));
    }
}

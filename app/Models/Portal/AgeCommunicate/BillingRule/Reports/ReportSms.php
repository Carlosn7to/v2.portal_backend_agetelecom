<?php

namespace App\Models\Portal\AgeCommunicate\BillingRule\Reports;

use App\Http\Controllers\Portal\AgeCommunicate\BillingRule\actions\sms\TemplatesSms;
use App\Models\Portal\AgeCommunicate\BillingRule\Templates\Template;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportSms extends Model
{
    use HasFactory;

    protected $connection = 'portal';
    protected $table = 'age_comunica_envios';
    protected $fillable = [
      'bulk_id',
        'mensagem_id',
        'canal',
        'contrato_id',
        'fatura_id',
        'celular',
        'celular_voalle',
        'email',
        'segregacao',
        'regra',
        'status',
        'status_descricao',
        'erro',
        'template_id',
        'informacoes_envio'
    ];

    const STATUS_MAP = [
        // Pendente
        1 => 'Pendente',
        3 => 'Espera',
        7 => 'Encaminhada',
        26 => 'Aceita',

        // Não-entregável
        4 => 'Rejeitada',
        9 => 'Não entregue',
        3041 => 'Desativado',

        // Entregue
        2 => 'Operadora',
        5 => 'Entregue',

        // Expirado
        15 => 'Expirado',
        29 => 'Desconhecido',
        87 => 'Bloqueado',

        // Rejeitado
        6 => 'Rede',
        8 => 'Prefixo',
        10 => 'DND',
        11 => 'Fonte',
        12 => 'Créditos',
        13 => 'Remetente',
        14 => 'Bloqueio',
        17 => 'Pacote',
        18 => 'Registrado',
        19 => 'Rota',
        20 => 'Inundação',
        21 => 'Sistema',
        23 => 'Duplicado',
        24 => 'UDH',
        25 => 'Longo',
        51 => 'Faltando',
        52 => 'Destino',





        // Personalizados
        100 => 'Enviado',
    ];

    public static function getStatusString($code) {
        return self::STATUS_MAP[$code] ?? 'desconhecido';  // retorna 'desconhecido' se o código não existir
    }

    // Mapeamento de códigos de status para suas descrições
    const STATUS_DESCRICAO_MAP = [
        // Pendente
        3 => 'A mensagem foi enviada com sucesso da nossa plataforma para o terminal da operadora, mas estamos aguardando o relatório de entrega do seu terminal. Caso a entrega em si seja afetada, as causas podem ser a disponibilidade do destino, problemas de rede, congestionamento ou requisitos adicionais de configuração.',
        7 => 'A mensagem foi aceita e processada com sucesso pela nossa plataforma e encaminhada para a próxima instância, que envolve especificamente a operadora móvel.',
        26 => 'A mensagem fica pendente após ser aceita pelo sistema e aguardando processamento posterior ou confirmação de entrega da operadora.',

        // Não-entregável
        4 => 'A mensagem foi rejeitada pela operadora e considerada não entregue, possivelmente devido a uma falha na rede ou restrições impostas pela operadora. As etapas de resolução incluem entrar em contato com o Suporte para maiores esclarecimentos ou resolução.',
        9 => 'A mensagem não foi entregue ao destinatário pretendido e foi considerada não entregue, normalmente devido a fatores como um número de destino inválido ou inacessível. As etapas de resolução envolvem a verificação da precisão do número do destinatário. Se o problema persistir, entre em contato com o suporte para obter assistência.',
        3041 => 'O número de telefone fornecido está listado como desativado, o que significa que não está mais ativo e não pode receber mensagens. É fundamental evitar enviar mais mensagens para este número.',

        // Entregue
        2 => 'A mensagem foi entregue com sucesso ao sistema da operadora e aguarda processamento adicional ou entrega ao aparelho do destinatário.',
        5 => 'A mensagem foi entregue com sucesso no aparelho do destinatário.',

        // Expirado
        15 => 'A mensagem foi recebida e enviada à operadora. Porém, ficou pendente até que o prazo de validade expire ou a operadora retorne o status EXPIRED.',
        29 => 'A mensagem expirou e o status da entrega é desconhecido, indicando que o relatório ou confirmação de entrega não foi recebido ou não pôde ser determinado.',
        87 => 'A mensagem foi rejeitada devido a um mecanismo antifraude.',

        // Rejeitado
        6 => 'A mensagem foi recebida, mas a rede está fora da nossa cobertura ou não está configurada na sua conta.',
        8 => 'A mensagem foi recebida, mas rejeitada porque o número não foi reconhecido devido a um prefixo ou comprimento incorreto.',
        10 => 'A mensagem foi recebida e rejeitada porque o usuário está inscrito nos serviços DND (Não perturbe), desativando qualquer tráfego de serviço para seu número.',
        11 => 'Sua conta está configurada para aceitar apenas IDs de remetentes registrados, e o ID de remetente definido na solicitação não foi registrado em sua conta.',
        12 => 'Sua conta está sem créditos para envio posterior. Recarregue sua conta.',
        13 => 'O ID do remetente foi colocado na lista de bloqueio da sua conta através da interface web da Infobip.',
        14 => 'O endereço de destino foi colocado na lista de bloqueio a pedido da operadora ou em sua conta por meio da interface da web.',
        17 => 'Os créditos da conta já passaram do período de validade. Recarregue sua subconta com créditos para estender o período de validade.',
        18 => 'Sua conta foi configurada para envio apenas para um único número para fins de teste.',
        19 => 'A mensagem foi recebida no sistema. Porém, sua conta não foi configurada para enviar mensagens, ou seja, nenhuma rota em sua conta está disponível para posterior envio.',
        20 => 'A mensagem foi rejeitada devido a um mecanismo anti-inundação. Por padrão, um único número só pode receber 20 mensagens variadas e 6 mensagens idênticas por hora.',
        21 => 'A solicitação foi rejeitada devido a um erro esperado do sistema. Tente enviar novamente ou entre em contato com nossa equipe de suporte técnico para obter mais detalhes.',
        23 => 'A solicitação foi rejeitada devido a um ID de mensagem duplicado especificado na solicitação de envio; os IDs das mensagens devem ser um valor exclusivo.',
        24 => 'A mensagem foi recebida e nosso sistema detectou que a mensagem foi formatada incorretamente devido a um parâmetro de classe ESM inválido (método API de mensagem binária com todos os recursos) ou a uma quantidade imprecisa de caracteres ao usar esmclass:64 (UDH).',
        25 => 'A mensagem foi recebida, mas o comprimento total da mensagem é superior a 25 partes ou o texto da mensagem excede 4.000 bytes de acordo com a limitação do sistema.',
        51 => 'A solicitação foi recebida, mas o to parâmetro não foi definido ou está vazio, ou seja, deve haver um destinatário válido para o qual enviar a mensagem.',
        52 => 'A solicitação foi recebida, mas o destino é inválido – o prefixo do número está incorreto porque não corresponde a um prefixo de número válido de nenhuma operadora móvel. O comprimento do número também é levado em consideração ao verificar a validade do número.',


        // Personalizado
        200 => 'Enviado pela aplicação Age Comunica, aguardando status de resposta InfoBip'
    ];

    /**
     * Método estático para obter a descrição do status.
     * @param int $code Código do status.
     * @return string Descrição do status ou 'desconhecido' se o código não estiver no mapa.
     */
    public static function getStatusDescription($code) {
        return self::STATUS_DESCRICAO_MAP[$code] ?? 'desconhecido';
    }

    public static function getAllSending()
    {
        return static::with('template')->get()->map->getAttributesForBuilding();
    }

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id', 'id')
            ->select('id', 'titulo', 'conteudo', 'status');
    }

    public function getAttributesForBuilding()
    {
        $this->load('template');

        $template = $this->template ? $this->template->toArray() : [];


        $attributes = [
            'bulk_id' => $this->bulk_id,
            'mensagem_id' => $this->mensagem_id,
            'contrato_id' => $this->contrato_id,
            'fatura_id' => $this->fatura_id,
            'celular' => $this->celular,
            'celular_voalle' => $this->celular_voalle,
            'segregacao' => $this->segregacao,
            'regra' => $this->regra,
            'status' => $this->getStatusString($this->status),
            'status_descricao' => $this->getStatusDescription($this->status_descricao),
            'erro' => $this->erro != null ? json_decode($this->erro, true) : null,
            'template' => $template
        ];

        return $attributes;
    }
}

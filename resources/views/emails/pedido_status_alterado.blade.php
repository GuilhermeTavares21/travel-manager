<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pedido atualizado - Onfly</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f5f7fa; padding: 20px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: auto; background: white; border-radius: 10px; overflow: hidden;">
        <tr>
            <td style="background-color: #009efb; color: white; padding: 20px; text-align: center; font-size: 22px; font-weight: bold;">
                Onfly
            </td>
        </tr>
        <tr>
            <td style="padding: 20px; color: #333;">
                <h2>Olá, {{ $pedido->nome_solicitante }}!</h2>
                <p>Seu pedido <strong>#{{ $pedido->id }}</strong> teve uma atualização de status.</p>
                <p><strong>Novo status:</strong> {{ ucfirst($novoStatus) }}</p>

                <h3>Detalhes do pedido:</h3>
                <ul>
                    <li><strong>Destino:</strong> {{ $pedido->destino ?? 'Não informado' }}</li>
                    <li><strong>Data de Ida:</strong> {{ $pedido->data_ida ?? 'Não informada' }}</li>
                    <li><strong>Data de Volta:</strong> {{ $pedido->data_volta ?? 'Não informada' }}</li>
                </ul>

                <p>Em breve, entraremos em contato com mais informações.</p>
                <p>Atenciosamente,<br><strong>Equipe Onfly</strong></p>
            </td>
        </tr>
        <tr>
            <td style="background-color: #009efb; color: white; text-align: center; padding: 10px;">
                © {{ date('Y') }} Onfly - Todos os direitos reservados
            </td>
        </tr>
    </table>
</body>
</html>

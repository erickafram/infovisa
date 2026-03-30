<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6;padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="500" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#2563eb,#4f46e5);padding:30px 40px;text-align:center;">
                            <h1 style="color:#ffffff;font-size:22px;margin:0;">🔒 InfoVISA</h1>
                            <p style="color:#bfdbfe;font-size:13px;margin:8px 0 0;">Recuperação de Senha</p>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding:30px 40px;">
                            <p style="color:#374151;font-size:15px;margin:0 0 15px;">Olá, <strong>{{ $nome }}</strong>!</p>
                            <p style="color:#6b7280;font-size:14px;line-height:1.6;margin:0 0 25px;">
                                Recebemos uma solicitação para redefinir a senha da sua conta no InfoVISA. Clique no botão abaixo para criar uma nova senha:
                            </p>
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:10px 0 25px;">
                                        <a href="{{ $link }}" style="display:inline-block;background-color:#2563eb;color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:8px;font-size:14px;font-weight:bold;">
                                            Redefinir Minha Senha
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="color:#9ca3af;font-size:12px;line-height:1.5;margin:0 0 15px;">
                                Este link expira em <strong>60 minutos</strong>. Se você não solicitou a recuperação de senha, ignore este e-mail.
                            </p>
                            <hr style="border:none;border-top:1px solid #e5e7eb;margin:20px 0;">
                            <p style="color:#9ca3af;font-size:11px;margin:0;">
                                Se o botão não funcionar, copie e cole este link no navegador:<br>
                                <a href="{{ $link }}" style="color:#2563eb;word-break:break-all;">{{ $link }}</a>
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f9fafb;padding:20px 40px;text-align:center;border-top:1px solid #e5e7eb;">
                            <p style="color:#9ca3af;font-size:11px;margin:0;">InfoVISA - Vigilância Sanitária do Tocantins</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

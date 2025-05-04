<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page {
            size: letter portrait;
            margin: 40px 50px 30px 50px;
        }
        body {
            font-family: "DejaVu Sans", sans-serif;
            color: #222;
        }
        .header {
            width: 100%;
            margin-bottom: 10px;
        }
        .header table {
            width: 100%;
        }
        .header td {
            vertical-align: middle;
        }
        .header-left {
            width: 30%;
        }
        .header-center {
            width: 40%;
            text-align: center;
        }
        .header-right {
            width: 30%;
            text-align: right;
        }
        .logo-ucordoba {
            height: 60px;
            width: 60px;
        }
        .logo-60 {
            height: 50px;
            width: 50px;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 0;
        }
        .subtitle {
            text-align: center;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .info-row {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-row table {
            width: 100%;
        }
        .info-row td {
            font-size: 12px;
        }
        .main-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            margin: 30px 0 10px 0;
        }
        .cert-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .cert-body {
            font-size: 12px;
            text-align: justify;
            margin: 0 10px 20px 10px;
        }
        .expide {
            font-size: 12px;
            margin: 20px 0 40px 0;
        }
        .firma {
            text-align: center;
            margin-top: 30px;
        }
        .firma-img {
            height: 50px;
            width: 50px;
            margin-bottom: 5px;
        }
        .firma-nombre {
            font-size: 12px;
            font-weight: bold;
        }
        .firma-cargo {
            font-size: 11px;
        }
        .footer {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 30px;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #444;
        }
        .footer-logo {
            height: 30px;
            width: 100px;
            margin-top: 10px;
        }
        .page-number {
            position: absolute;
            right: 50px;
            bottom: 10px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td class="header-left">
                    <img src="C:/Users/DELL/Documents/universidad/semillero_api/semillero_api/public/img/logo_ucordoba.png" class="logo-ucordoba">
                </td>
                <td class="header-center">
                    <div class="title">Universidad de Córdoba</div>
                    <div class="subtitle">OFICINA DE EVENTOS Y ADMINISTRACIÓN DE PROYECTOS</div>
                </td>
                <td class="header-right">
                    <img src="C:/Users/DELL/Documents/universidad/semillero_api/semillero_api/public/img/logo_60.png" class="logo-60">
                </td>
            </tr>
        </table>
    </div>

    <div class="info-row">
        <table>
            <tr>
                <td>Montería, {{ $fecha }}</td>
                <td style="text-align: right">CÓDIGO DE VERIFICACIÓN: {{ $codigo ?? 'XXXXXX' }}</td>
            </tr>
        </table>
    </div>

    <div class="main-title">EL COORDINADOR DE EVENTOS Y ADMINISTRACIÓN DE PROYECTOS</div>
    <div class="cert-title">CERTIFICA</div>
    <div class="cert-body">
        Que <b>{{ $autor }}</b> identificado(a) con CC Número <b>{{ $documento }}</b> Expedida en <b>{{ $expedida }}</b> ha participado en el semillero de investigación '<b>{{ $semillero }}</b>' con el proyecto titulado '<b>{{ $proyecto }}</b>', demostrando un alto nivel de compromiso y dedicación.
    </div>

    <div class="firma">
        <img src="C:/Users/DELL/Documents/universidad/semillero_api/semillero_api/public/img/firma.png" class="firma-img"><br>
        <div class="firma-nombre">JUAN PABLO OYOLA CORDOBA</div>
        <div class="firma-cargo">COORDINADOR DE EVENTOS Y ADMINISTRACIÓN DE PROYECTOS</div>
    </div>

    <div class="footer">
        Para validar la autenticidad de este certificado, ingrese a la página Web de la Universidad de Córdoba, Opción Verificar Código. El código de verificación se encuentra en la parte superior de la primera página de este documento.
        <br>
        <img src="C:/Users/DELL/Documents/universidad/semillero_api/semillero_api/public/img/footer_ucordoba.png" class="footer-logo">
    </div>
    <div class="page-number">Página 1 de 1</div>
</body>
</html> 
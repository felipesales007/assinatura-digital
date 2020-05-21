<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use TCPDF;

class PDFController extends Controller
{
    public function createPDF(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Obtendo e manipulando o certificado
        |--------------------------------------------------------------------------
        */

        // pasta local pública onde o certificado .pfx será salvo temporariamente
        $path = base_path() . '/public/storage/certificate';

        // arquivo .pfx
        $file = $request->file;

        // criptografia do nome do arquivo .pfx
        $filename = md5($file->getClientOriginalName() . '-' . implode('-', explode(':', date('H:i:s')))) . '.' . $file->getClientOriginalExtension();

        // salvando o arquivo .pfx no local público temporariamente
        $file->move($path, $filename);

        // obtendo o caminho do arquivo .pfx onde ele foi salvo temporariamente
        $certificate = 'file://' . $path . '/' . $filename;

        // pegando o conteúdo do certificado .pfx
        $data = file_get_contents($certificate);

        // verificando se o conteúdo do certificado .pfx é válido
        if (openssl_pkcs12_read($data, $certs, $request->password)) {
            // coloca os dados do certificado em $certs['cert'] e a chave privada em $certs['pkey']
            file_put_contents($certificate, $certs['cert'] . $certs['pkey']);

            // converte o certificado e pega os dados do proprietário
            $content = openssl_x509_parse(openssl_x509_read($certs['cert']));

            //dd($content); // todos os dados do certificado
            //dd($content['subject']['C']); // país
            //dd($content['subject']['ST']); // estado
            //dd($content['subject']['L']); // município
            //dd($content['subject']['CN']); // razão social e CNPJ/CPF
            //dd(date('d/m/Y', $content['validTo_time_t'])); // validade do certificado
            //dd($content['extensions']['subjectAltName']);	// e-mails cadastrados

            // variável da razão social e CNPJ/CPF
            $info = explode(':', $content['subject']['CN']);

            // salva o nome da empresa
            $company = $info[0];

            // verifica se o registro é um CNPJ ou CPF e salva o registro
            if (strlen($info[1]) == 14) {
                $type = 'CNPJ: ' . $info[1];
            } else {
                $type = 'CPF: ' . $info[1];
            }

            // informação para a assinatura digital, contendo o nome da empresa, nº do registro, data e hora da assinatura
            $info = $company . "\n" . $type . "\n" . 'Data: ' . date('d/m/Y H:i');
        } else {
            // removendo o arquivo .pfx do local público
            File::delete($certificate);

            // se o conteúdo do arquivo .pfx for inválido
            return back()->with('status', 'Arquivo .pfx ou senha inválida.');
        }

        /*
        |--------------------------------------------------------------------------
        | Criando o PDF e assinando com o certificado
        |--------------------------------------------------------------------------
        */

        // criando e configurando o PDF
        $pdf = new TCPDF('P', 'mm', 'P', true, 'UTF-8', false);

        // criando o cabeçalho do PDF com as informações do certificado
        $pdf->SetHeaderData('', 2, 'Assinado de forma digital por', $info, array(0, 0, 0), array(255, 255, 255));

        // definindo a fonte do cabeçalho do PDF
        $pdf->setHeaderFont(['helvetica', '', 9]);

        // definindo as margens do cabeçalho do PDF
        $pdf->SetMargins(15, 21, 15);
        $pdf->SetHeaderMargin(5);

        // removendo o rodapé do PDF
        $pdf->setPrintFooter(false);

        // assinando o PDF com a certificado (assinatura digital)
        $pdf->setSignature($certificate, $certificate, '', '', 2, '', 'A');

        // definindo a fonte e o título da página do PDF
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetTitle('Assinado de forma digital');
        $pdf->AddPage();

        // view contendo a página PDF que será imprimida
        $text = view('pdf');

        // área do certificado para clique no PDF
        $pdf->setSignatureAppearance(14.5, 3, 95, 20);

        // adicionando o conteúdo do certificado e do PDF para impressão
        $pdf->writeHTML($text, true, 0, true, 0);

        // modo de impressão
        //$pdf->Output(public_path('arquivo.pdf'), 'F'); // salva em um diretório
        //$pdf->Output(public_path('arquivo.pdf'), 'D'); // baixa o pdf automaticamente
        $pdf->Output('arquivo.pdf', 'I'); // abre no navegador

        // removendo o arquivo .pfx do local público
        File::delete($certificate);
    }
}

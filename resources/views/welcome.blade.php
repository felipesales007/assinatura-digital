<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Assinatura Digital</title>

        <!-- css -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    </head>
    <style>
        #cover {
            display: flex;
            position: relative;
            height: 100%;
            background: #222 url('https://unsplash.it/1080/720/?random') center center no-repeat;
            background-size: cover;
            align-items: center;
        }

        #cover-caption {
            width: 100%;
            position: relative;
            z-index: 1;
        }

        form:before {
            content: '';
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            background-color: rgba(0,0,0,0.3);
            border-radius: 10px;
            z-index: -1;
        }
    </style>
    <body>
        <!-- corpo -->
        <section id="cover" class="min-vh-100">
            <div id="cover-caption">
                <!-- conteúdo -->
                <div class="container">
                    <div class="row text-white">
                        <!-- responsividade -->
                        <div class="col-xl-5 col-lg-6 col-md-8 col-sm-10 mx-auto form p-4">
                            <!-- título -->
                            <h3 class="py-2 text-truncate">Assinar documento</h3>
                            <div class="px-2">
                                <!-- formulário -->
                                <form action="{{ route('pdf') }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    @if (session('status'))
                                        <!-- alerta de erro -->
                                        <div class="alert alert-danger" role="alert">{{ session('status') }}</div>
                                    @endif
                                    <!-- arquivo -->
                                    <div class="form-group">
                                        <label for="file">Arquivo</label>
                                        <div class="custom-file">
                                            <input type="file" id="file" name="file" class="custom-file-input" required accept=".pfx">
                                            <label class="custom-file-label" for="file" data-browse="Selecionar">Certificado .pfx</label>
                                        </div>
                                        <small class="form-text text-white">Arquivo no formato/extensão .pfx</small>
                                    </div>
                                    <!-- senha -->
                                    <div class="form-group">
                                        <label for="password">Senha</label>
                                        <input type="password" id="password" name="password" class="form-control" placeholder="Senha" required>
                                    </div>
                                    <!-- botão -->
                                    <button type="submit" class="btn btn-primary btn-lg mt-3">Gerar assinatura</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- js -->
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script>
            $('input[type="file"]').change(function(e) {
                $('.custom-file-label').html(e.target.files[0].name);
            });
        </script>
    </body>
</html>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Admin | Login</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Russo+One&display=swap");

        svg {
            font-family: "Russo One", sans-serif;
            width: 100%; height: 100%;
        }
        svg text {
            animation: stroke 6s infinite alternate;
            stroke-width: 1px;
            stroke: #365FA0;
            font-size: 50px;
        }
        @keyframes stroke {
            0%   {
                fill: rgba(72,138,204,0); stroke: rgba(54,95,160,1);
                stroke-dashoffset: 25%; stroke-dasharray: 0 50%; stroke-width: 2;
            }
            70%  {fill: rgba(72,138,204,0); stroke: rgba(54,95,160,1); }
            80%  {fill: rgba(72,138,204,0); stroke: rgba(54,95,160,1); stroke-width: 3; }
            100% {
                fill: rgba(72,138,204,1); stroke: rgba(54,95,160,0);
                stroke-dashoffset: -25%; stroke-dasharray: 50% 0; stroke-width: 0;
            }
        }

        .wrapper {
            background-color: #00000031;
            backdrop-filter: blur(2px);
            width: 100%;
            height: 100%;
        }

        .loader {
            width: 100vw;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 9999;
            display: none
        }

        #errors {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            display: flex;
            flex-direction: column;
            max-width: calc(100% - 1.25rem * 2);
            gap: 1rem;
            z-index: 99999999999999999999;
        }

        #errors >* {
            width: 100%;
            color: #fff;
            font-size: 1.1rem;
            padding: 1rem;
            border-radius: 1rem;
            box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;
        }

        #errors .error {
            background: #e41749;
        }
        #errors .success {
            background: #12c99b;
        }

    </style>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('/admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('/admin/css/sb-admin-2.min.css') }}" rel="stylesheet">

</head>

<body style="  background-color: #ececec;">
    <div id="errors"></div>
    <div class="loader">
        <div class="wrapper">
            <svg>
                <text x="50%" y="50%" dy=".35em" text-anchor="middle">
                    Login ...
                </text>
            </svg>
        </div>
    </div>

    <div class="container" id="login" style="min-height: 100vh;display: flex;justify-content: center;align-items: center;">

        <!-- Outer Row -->
        <div class="row justify-content-center" style="min-width: 100%;">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block">
                                <img src="{{ asset('/admin/img/glenn-carstens-peters-npxXWgQ33ZQ-unsplash.jpg') }}" alt="" style="width: 100%;height: 100%;object-fit: cover;position: absolute;">
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Admin!</h1>
                                    </div>
                                    <form class="user" @submit.prevent>
                                        <div class="form-group">
                                            <input type="email" class="form-control form-control-user"
                                                id="exampleInputEmail" aria-describedby="emailHelp"
                                                placeholder="Enter Email Address..." v-model="email">
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user"
                                                id="exampleInputPassword" placeholder="Password" v-model="password">
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="customCheck">
                                                <label class="custom-control-label" for="customCheck">Remember
                                                    Me</label>
                                            </div>
                                        </div>
                                        <a @click="login(this.email, this.password)" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('/admin/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('/admin/vendor/axios/axios.min.js') }}"></script>
    <script src="{{ asset('/admin/vendor/vue/vue.min.js') }}"></script>
    <script src="{{ asset('/admin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('/admin/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('/admin/js/sb-admin-2.min.js') }}"></script>

    <script>
        const { createApp, ref } = Vue

        createApp({
            data() {
                return {
                    email: null,
                    password: null
                }
            },
            methods: {
                async login(email, password) {
                    $('.loader').fadeIn().css('display', 'flex')
                    try {
                        const response = await axios.post(`{{ route("admin.login.post") }}`, {
                            email: email,
                            password: password,
                        },
                        );
                        if (response.data.status === true) {
                            document.getElementById('errors').innerHTML = ''
                            let error = document.createElement('div')
                            error.classList = 'success'
                            error.innerHTML = response.data.message
                            document.getElementById('errors').append(error)
                            $('#errors').fadeIn('slow')
                            setTimeout(() => {
                                $('.loader').fadeOut()
                                $('#errors').fadeOut('slow')
                                window.location.href = '/admin/dashboard'
                            }, 1300);
                        } else {
                            $('.loader').fadeOut()
                            document.getElementById('errors').innerHTML = ''
                            $.each(response.data.errors, function (key, value) {
                                let error = document.createElement('div')
                                error.classList = 'error'
                                error.innerHTML = value
                                document.getElementById('errors').append(error)
                            });
                            $('#errors').fadeIn('slow')
                            setTimeout(() => {
                                $('#errors').fadeOut('slow')
                            }, 5000);
                        }

                    } catch (error) {
                        document.getElementById('errors').innerHTML = ''
                        let err = document.createElement('div')
                        err.classList = 'error'
                        err.innerHTML = 'server error try again later'
                        document.getElementById('errors').append(err)
                        $('#errors').fadeIn('slow')
                        $('.loader').fadeOut()

                        setTimeout(() => {
                            $('#errors').fadeOut('slow')
                        }, 3500);

                        console.error(error);
                    }
                }
            },
        }).mount('#login')

    </script>
</body>

</html>

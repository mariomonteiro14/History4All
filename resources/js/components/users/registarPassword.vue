<template>
    <div>
        <br><br>
        <div v-if="user.id" class="container py-5">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6 mx-auto">
                            <div class="card rounded-0">
                                <div class="card-header">
                                    <h3 class="mb-0">Definir Password</h3>
                                </div>
                                <div class="card-body">
                                    <form class="form" role="form" autocomplete="off" id="formLogin" novalidate=""
                                          method="POST">
                                        <br>
                                        <div class="form-group">
                                            <v-text-field id="inputPassword"
                                                          v-model="user.password"
                                                          label="Nova Password"
                                                          :rules="[() => !(user.password && user.password.length < 4) || 'minimo 4 caracteres']"
                                                          :type="'password'"
                                                          required
                                            ></v-text-field>
                                        </div>

                                        <div class="form-group">
                                            <v-text-field id="inputPasswordConf"
                                                          v-model="passwordConfirmation"
                                                          label="Confirmar Password"
                                                          :type="'password'"
                                                          :error-messages="passwordMatchError"
                                                          required
                                            ></v-text-field>
                                        </div>
                                        <br>
                                        <div class="custom-file">
                                            <label class="custom-file-label" for="upload-files">{{getFilesText}}</label>
                                            <input id="upload-files" type="file" multiple
                                                   class="form-control custom-file-input"
                                                   @change="handleFile">
                                        </div>
                                        <br><br>
                                        <button v-if="!aEnviar" type="submit" class="btn btn-success btn-lg float-right" id="btnLogin"
                                                v-on:click.prevent="formValidateAndSend" :disabled="!formCompleted">
                                            Guardar
                                        </button>
                                        <loader v-else color="green" size="32px"></loader>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br><br><br>
    </div>
</template>

<script>
    export default {
        props: ['token'],
        created() {
            this.getUser();
        },
        data: function () {
            return {
                user: {},
                passwordConfirmation: '',
                foto: '',
                aEnviar: false,
            };
        },
        methods: {
            getUser: function () {
                axios.get("/api/users/token/" + this.token).then(response => {
                    if (response.data === "" && response.data.email_verified_at === null) {//token inválido
                        this.$router.push({name: 'index'});
                    }
                    this.user = response.data;
                }).catch(error => {
                    this.toastErrorApi(error);
                });
            },
            formValidateAndSend() {
                if (this.user.password == this.passwordConfirmation) {
                    const config = {
                        headers: {'content-type': 'multipart/form-data'}
                    };
                    this.aEnviar = true;
                    axios.post('/api/register/activate/' + this.user.id, this.formCreate(), config).then(response => {
                        this.toastPopUp('success', 'Conta Ativada');
                        this.aEnviar = false;
                        this.$router.push('/');
                    }).catch(error => {
                        this.aEnviar = false;
                        this.toastErrorApi(error);
                    });
                } else {
                    this.toastPopUp("error", "A password e a passowrd de confirmação são diferentes");
                }
            },
            handleFile: function (e) {
                let files = e.target.files || e.dataTransfer.files;

                if (!files.length || files.length > 1) {
                    return false;
                }

                if (!files[0].type.includes("image/")) {
                    this.toastPopUp('error', 'Ficheiro tem de ser uma imagem');
                    return;
                }

                this.foto = files[0];
            },

            formCreate: function () {
                let formData = new FormData();
                formData.append('password', this.user.password);
                if (this.foto != '') {
                    formData.append('foto', this.foto);
                }

                return formData;
            },
        },
        computed: {
            formCompleted() {
                return this.passwordConfirmation && this.user.password && this.user.password.length>=4 && this.passwordConfirmation == this.user.password;
            },
            passwordMatchError() {
                if (!this.user.password || this.user.password === "") {
                    return '';
                }
                if (this.passwordConfirmation === "") {
                    return 'Confirmação obrigatoria'
                }
                return (this.user.password === this.passwordConfirmation) ? '' : 'Password nao combinam'
            },
            getFilesText() {
                if (this.foto == "") {
                    return "Adicione foto de perfil";
                } else {
                    return "Foto carregada";
                }
            }
        },


    }
</script>

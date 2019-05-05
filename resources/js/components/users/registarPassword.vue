<template>
    <div>
        <br><br>
        <div v-if="user.id"class="container py-5">
            <div class="row">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6 mx-auto">
                            <div class="card rounded-0">
                                <div class="card-header">
                                    <h3 class="mb-0">Definir Password</h3>
                                </div>
                                <div class="card-body">
                                    <form class="form" role="form" autocomplete="off" id="formLogin" novalidate="" method="POST">
                                        <div class="form-group">
                                            <label for="uname1">Password</label>
                                            <input type="password" class="form-control form-control-lg rounded-0" name="uname1" id="uname1" required="" v-model.trim="user.password">
                                        </div>
                                        <div class="form-group">
                                            <label>Confirmar Password</label>
                                            <input type="password" class="form-control form-control-lg rounded-0" id="pwd1" required="" autocomplete="new-password" v-model.trim="passwordConfirmation">
                                        </div>
                                        <br>
                                        <div class="custom-file">
                                            <label class="custom-file-label" for="upload-files">Adicione Foto de Perfil</label>
                                            <input id="upload-files" type="file" multiple class="form-control custom-file-input"
                                                   @change="handleFile">
                                        </div>
                                        <br><br>
                                        <button type="submit" class="btn btn-success btn-lg float-right" id="btnLogin" v-on:click.prevent="formValidateAndSend" :disabled="!formCompleted">Guardar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
            };
        },
        methods: {
            getUser: function () {
                axios.get("/api/users/token/" + this.token).then(response => {
                    if (response.data === ""){//token inválido
                        this.$router.push('/');
                    }
                    this.user = response.data;
                }).catch(errors => {
                    console.log(errors);
                });
            },
            formValidateAndSend(){
                if(this.user.password == this.passwordConfirmation){
                    const config = {
                        headers: {'content-type': 'multipart/form-data'}
                    };
                    axios.post('/api/register/activate/' + this.user.id, this.formCreate(), config).then(response => {
                        this.toastPopUp('success','Conta Ativada');
                        this.$router.push('/');
                    }).catch(error => {
                        this.$toasted.error(`Erro ativando conta`, {
                            position: "bottom-center",
                            duration: 3000,
                        });
                    });
                }else{
                    this.$toasted.error(`Passwords don't match`, {
                        position: "bottom-center",
                        duration: 3000,
                    });
                }
            },
            handleFile: function (e) {
                let files = e.target.files || e.dataTransfer.files;

                if (!files.length || files.length > 1) {
                    return false;
                }

                if (!files[0].type.includes("image/")) {
                    this.toastPopUp('error','Ficheiro tem de ser uma imagem');
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
            formCompleted(){
                return this.passwordConfirmation && this.user.password;
            }
        },


    }
</script>

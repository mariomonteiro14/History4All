<template>
    <div>
        <!-- Modal Add Order-->
        <div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModal"
             aria-hidden="true" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editProfileModal">Registar Escola</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close" :disabled="isLoading">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <br>

                        <div class="form-group">
                            <v-text-field id="inputNome"
                                          v-model="user.nome"
                                          label="Nome"
                                          readonly
                                          required
                            ></v-text-field>
                        </div>
                        <div class="form-group">
                            <v-text-field id="inputEmail"
                                          v-model="user.email"
                                          label="Email"
                                          :rules="[
                                          (v) => !!v || 'Email é obrigatório',
                                          (v) => /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'email tem de ser valido'
                                          ]"
                                          required
                            ></v-text-field>
                        </div>
                        <div class="form-group" v-if="user.email != $store.state.user.email">
                            <v-text-field id="inputEmailConf"
                                          v-model="emailConf"
                                          label="Confirmar Novo Email"
                                          :error-messages="emailMatchError"
                                          required
                            ></v-text-field>
                        </div>
                        <div class="form-group">
                            <v-text-field id="inputPassword"
                                          v-model="user.password"
                                          label="Nova Password"
                                          :rules="[() => !(user.password && user.password.length < 4) || 'minimo 4 caracteres']"
                                          :type="'password'"
                                          required
                            ></v-text-field>
                        </div>

                        <div class="form-group" v-if="user.password">
                            <v-text-field id="inputPasswordConf"
                                          v-model="passwordConf"
                                          label="Confirmar Password"
                                          :type="'password'"
                                          :error-messages="passwordMatchError"
                                          required
                            ></v-text-field>
                        </div>
                        <v-layout class="custom-file" fluid wrap align-center>
                            <label class="custom-file-label" for="upload-files">{{getFilesText}}</label>
                            <input id="upload-files" type="file" class="form-control custom-file-input"
                                   @change="handleFile">
                            <v-btn v-if="foto !=''" @click="foto=''">Limpar</v-btn>
                            </v-layout>
                        <br>
                    </div>
                    <div v-if="!isLoading" class="modal-footer">
                            <button class="btn btn-info" v-on:click.prevent="save" :disabled="hasErrors">Guardar
                            </button>
                            <button flat class="btn btn-danger" v-on:click.prevent="cancel">Cancelar</button>
                    </div>
                    <div v-else class="modal-footer">
                        <loader color="green" size="32px"></loader>
                    </div>
                </div>
            </div>
        </div>

        <v-dialog v-model="dialog" max-width="290">
            <v-card>
                <v-card-title class="headline">Atenção</v-card-title>
                <v-card-text>Novo email:</v-card-text>
                <v-card-text>Irá receber um email de confirmaçao do novo email. O email só será alterado após a sua confirmaçao</v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="success" flat="flat" @click="dialog = false">
                        Ok
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
    export default {

        mounted() {
            this.user = Object.assign({}, this.$store.state.user);
            delete this.user.password;
        },
        data: function () {
            return {
                user: {},
                emailConf: '',
                passwordConf: '',
                foto: '',
                isLoading: false,
                dialog: false,
            }
        },
        methods: {
            save: function () {
                const config = {
                    headers: {'content-type': 'multipart/form-data'}
                };
                this.isLoading = true;
                axios.post('/api/users/me', this.formCreate(), config).then(response => {
                    this.toastPopUp("success", "Informação Atualizada!");
                    this.$store.commit('setUser',response.data.data);
                    $('#editProfileModal').modal('hide');
                    if (this.user.email !== this.$store.state.user.email){
                        this.dialog = true;
                    }
                    this.$emit('reloadUser');
                    this.isLoading = false;

                }).catch(error => {
                    this.isLoading = false;
                    this.toastErrorApi(error);
                })
            },

            cancel: function () {
                $('#editProfileModal').modal('hide');
                this.user = Object.assign({}, this.$store.state.user);
                delete this.user.password;
                this.emailConf = '';
                this.passwordConf= '';
                this.foto= '';
                this.isLoading= false;
            },
            handleFile: function (e) {
                let files = e.target.files || e.dataTransfer.files;

                if (!files.length || files.length > 1) {
                    return false;
                }

                if (!files[0].type.includes("image/")) {
                    this.toastPopUp('error', 'Ficheiro invalido: tem de ser uma imagem');
                    return;
                }

                this.foto = files[0];
            },

            formCreate: function () {
                let formData = new FormData();
                formData.append('newEmail', this.user.email);
                if (this.user.password && this.user.password.length > 3) {
                    formData.append('newPassword', this.user.password);
                }
                if (this.foto !== "") {
                    formData.append('newFoto', this.foto);
                }

                return formData;
            },

        },

        computed: {
            hasErrors: function () {
                if (!this.user.email || (this.user.password && !this.passwordConf || !this.user.password && this.passwordConf)
                    || (this.user.password && this.user.password !== this.passwordConf)) {
                    return true;
                }
                if (this.user.password && (this.user.password.length > 0 && this.user.password.length < 4)) {
                    return true;
                }

                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                if (!re.test(String(this.user.email).toLowerCase())) {
                    return true;
                }

                if (this.user.email != this.$store.state.user.email && this.user.email !== this.emailConf) {
                    return true;
                }
                return false;
            },
            emailMatchError() {
                if (this.user.email === "" || this.user.email == this.$store.state.user.email) {
                    return '';
                }
                if (this.emailConf === "") {
                    return 'Confirmação obrigatoria'
                }
                return (this.user.email === this.emailConf) ? '' : 'Emails nao combinam'
            },
            passwordMatchError() {
                if (!this.user.password || this.user.password === "") {
                    return '';
                }
                if (this.passwordConf === "") {
                    return 'Confirmação obrigatoria'
                }
                return (this.user.password === this.passwordConf) ? '' : 'Password nao combinam'
            },
            getFilesText() {
                if (this.foto == "") {
                    return "Carregue nova foto de perfil";
                } else {
                    return "Foto carregada";
                }
            }
        },
    }
    ;

</script>


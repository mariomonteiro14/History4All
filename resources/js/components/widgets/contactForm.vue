<template>
    <div>
        <!-- Modal Add Order-->
        <div class="modal fade" id="contactModal" tabindex="-1" role="dialog" aria-labelledby="contactModal"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title" id="contactModal">{{getTitle}}</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close" :disabled="aEnviar">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="form-group">
                            <v-text-field id="inputAssunto"
                                          v-model="mensagem.assunto"
                                          label="Assunto"
                                          :rules="[v => !!v || 'Assunto é obrigatório',
                                                   v => v.length >= 6 || 'Minimo 6 caracteres']"
                                          clearable
                                          required
                            ></v-text-field>
                        </div>
                        <div class="form-group" v-if="emailTo === '' && !$store.state.user">
                            <v-text-field id="inputEmail"
                                          v-model="mensagem.email"
                                          label="Seu email"
                                          :rules="emailRules"
                                          clearable
                                          required
                            ></v-text-field>
                        </div>
                        <div class="form-group">
                            <v-textarea
                                id="inputMensagem"
                                v-model="mensagem.texto"
                                label="Mensagem"
                                :rules="[v => !!v || 'Mensagem é obrigatória',
                                             v => v && v.length >= 10 || 'minimo 10 caracteres']"
                                counter="1000"
                                required
                            ></v-textarea>
                        </div>

                    </div>

                    <div class="modal-footer" v-if="!aEnviar">
                        <button class="btn btn-info" :disabled="hasErrors" v-on:click.prevent="save">Enviar</button>
                        <button class="btn btn-danger" v-on:click.prevent="cancel">Cancelar</button>
                    </div>
                    <div v-else class="modal-footer">
                        <loader color="green" size="32px"></loader>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    export default {

        props: ['emailTo'],
        mounted() {
            if (this.emailTo) {
                this.mensagem.emailPara = this.emailTo;
            }
            if (this.$store.state.user) {
                this.mensagem.email = this.$store.state.user.email;
            }
        },

        data: function () {
            return {
                mensagem: {
                    assunto: '',
                    email: '',
                    texto: '',
                    emailPara: null
                },
                emailRules: [
                    (v) => !!v || 'email é obrigatório',
                    (v) => /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'email tem de ser valido'
                ],
                aEnviar: false
            };
        },
        methods: {

            save: function () {
                this.aEnviar = true;
                axios.post('/api/sendEmail/history4all', this.mensagem).then(response => {
                    this.toastPopUp("success", "Mensagem Enviada");
                    this.cleanForm();
                    $('#contactModal').modal('hide');
                    this.aEnviar = false;
                }).catch(error => {
                    this.aEnviar = false;
                    this.toastPopUp("error", `${error.response.data.message}`);
                })
            },

            cancel: function () {
                this.cleanForm();
                $('#contactModal').modal('hide');
            },
            cleanForm: function () {
                this.mensagem.assunto = "";
                this.mensagem.email = "";
                this.mensagem.texto = "";
            },


        },
        computed: {
            hasErrors: function () {
                if (!this.mensagem.email) {
                    return true;
                }

                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                if (!re.test(String(this.mensagem.email).toLowerCase())) {
                    return true;
                }
                if (!this.mensagem.assunto || this.mensagem.assunto.length < 6) {
                    return true;
                }
                if (!this.mensagem.texto || this.mensagem.texto.length < 10) {
                    return true;
                }

                return false;
            },
            getTitle: function () {
                return this.emailTo ? "Contactar utilizador via email" : "Contactar History4All";
            },
        },
    }

</script>

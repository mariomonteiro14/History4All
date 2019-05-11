<template>
    <div>
        <!-- Modal Add Order-->
        <div class="modal fade" id="contactModal" tabindex="-1" role="dialog" aria-labelledby="contactModal"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title" id="contactModal">Contactar History4All</h5>
                            <button type="button" @click="cancel()" class="close" data-dismiss="modal"
                                    aria-label="Close">
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
                        <div class="form-group" v-if="emailFrom == ''">
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
                                             v => v && v.length >= 75 || 'minimo 75 caracteres']"
                                counter="1000"
                                required
                            ></v-textarea>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-info" :disabled="hasErrors" v-on:click.prevent="save">Enviar</button>
                        <button class="btn btn-danger" v-on:click.prevent="cancel">Cancelar</button>
                    </div>

                </div>

            </div>
        </div>
    </div>
</template>

<script>
    export default {

        props:['emailFrom'],
        mounted(){
            if (this.emailFrom){
                this.mensagem.email = this.emailFrom;
            }
        },

        data: function () {
            return {
                mensagem: {
                    assunto: '',
                    email: '',
                    texto: '',
                },
                emailRules: [
                    (v) => !!v || 'email é obrigatório',
                    (v) => /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'email tem de ser valido'
                ],
            };
        },
        methods: {

            save: function () {
                axios.post('/api/sendEmail/history4all', this.mensagem).then(response => {
                    this.toastPopUp("success", "Mensagem Enviada");
                    this.cleanForm();
                    $('#contactModal').modal('hide');
                }).catch(error => {
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
                if (!this.mensagem.email){
                    return true;
                }

                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                if (!re.test(String(this.mensagem.email).toLowerCase())) {
                    return true;
                }
                if (!this.mensagem.assunto || this.mensagem.assunto.length < 6) {
                    return true;
                }
                if (!this.mensagem.texto || this.mensagem.texto.length < 75) {
                    return true;
                }

                return false;
            },
        },
    }

</script>

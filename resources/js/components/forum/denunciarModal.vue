<template>
    <div>
        <v-dialog persistent v-model="show" width="450px">
            <v-card v-if="!isSended">
                <v-card-title class="headline">{{titulo}}</v-card-title>

                <v-card-text>
                    <h4>Motivo:</h4>
                    <v-radio-group
                        v-model="denuncia"
                        style="width: 400px"
                        hide-details
                        :disabled="isSending"
                    >
                        <v-radio color="green" value="Conteudo imprópio" label="Conteudo imprópio"></v-radio>
                        <v-radio color="green" value="Conteudo irrelevante ao tema" label="Conteudo irrelevante ao tema"></v-radio>
                        <v-radio color="green" value="Linguagem explicita" label="Linguagem explicita"></v-radio>
                        <v-layout >
                            <v-radio color="green" value="outro" :label="denuncia != 'outro' ? 'Outro' : ''"></v-radio>
                            <v-text-field
                                v-if="denuncia=='outro'"
                                color="green"
                                placeholder="Outro..."
                                v-model="outro"
                                regular
                                size="250px"
                                :disabled="isSending"
                            ></v-text-field>

                        </v-layout>
                    </v-radio-group>

                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn v-if="!isSending"
                           flat round color="primary"
                           @click="closeDialog">Cancelar
                    </v-btn>

                    <v-btn v-if="!isSending"
                           color="green"
                           text flat
                           @click="registarDenuncia()"
                           :disabled="denuncia.length < 3 || (denuncia=='outro' && outro.length < 4)"
                    >
                        Enviar
                    </v-btn>
                    <loader v-else style="padding: 10px" color="green" size="32px"></loader>
                </v-card-actions>
            </v-card>
            <v-card v-else>
                <v-card-text>
                    <br>
                    <center>
                        <v-icon size="50px" color="green">fa fa-check-circle</v-icon>
                        <br>
                        <br>
                        <span class="green--text">Denuncia registada!</span><br>
                        <span class="green--text"> Obrigado pelo seu contributo.</span>
                    </center>

                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="closeDialog" color="green" flat round>OK</v-btn>
                    <v-spacer></v-spacer>
                </v-card-actions>
            </v-card>

        </v-dialog>
    </div>
</template>

<script>
    export default {

        props: ['isForum', 'id'],

        data: function () {
            return {
                show: false,
                isSending: false,
                isSended: false,
                denuncia: '',
                outro: '',
            };
        },

        methods: {
            registarDenuncia() {
                this.isSending = true;

                let url = '';
                if (this.isForum == true) {
                    url = '/api/forums/denunciar/forum/' + this.id;
                } else {
                    url = '/api/forums/denunciar/comentario/' + this.id;
                }

                axios.post(url, {'denuncia':this.denuncia == "outro"? this.outro : this.denuncia}).then(response => {
                    this.isSending = false;
                    this.isSended = true;
                }).catch(error => {
                    this.isSending = false
                    this.toastErrorApi(error);
                })
            },
            closeDialog() {
                this.show = false;
                setTimeout(function(){
                    this.isSended = false;
                    this.isSending = false;
                    this.denuncia = "";
                    this.outro = "";
                    console.log("fechar")
                }.bind(this), 500);


            },
            openDialog() {
                this.show = true;
            }
        },
        computed: {
            titulo: function () {
                if (this.isForum==true) {
                    return "Denunciar Forum"
                }
                return "Denunciar Comentário"
            },

        },
        watch: {}
    };

</script>

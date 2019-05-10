<template>
    <div id="app">
        <br><br><br><br>

        <v-app id="inspire">
            <v-layout justify-center>
                <v-flex xs12 sm12 md12>
                    <v-card>
                        <v-container fluid grid-list-md>
                            <v-flex v-if="atividade">
                                <v-layout row wrap>
                                    <v-toolbar flat>
                                        <v-toolbar-title>
                                            <h3 class="headline mb-0">{{atividade.titulo}}</h3>
                                            {{atividade.tipo}}
                                        </v-toolbar-title>
                                        <v-btn v-if="estado == 'coordenador'" color="info" @click.stop="notificacaoDialog = true; mensagemNotificacao = ''">
                                            enviar notificação
                                        </v-btn>
                                    </v-toolbar>
                                    <v-flex :xs12="!atividade.chat" :xs8="atividade.chat && (estado == 'pendente' || estado == 'coordenador')">
                                        <v-card>
                                            <v-card-text>
                                                <span>{{atividade.descricao}}</span>
                                                <br><br><br><br>
                                                <p v-if="atividade.coordenador">Coordenador: {{atividade.coordenador.nome}}</p>
                                                Número de elementos por grupo: {{atividade.numeroElementos}}
                                                <div>
                                                    <v-btn small @click="showPatrimonios()">Patrimónios relacionados</v-btn>
                                                </div>
                                                <p v-if="!estado">
                                                    <v-btn color="info" @click="participar()">Participar</v-btn><span> - data de término: {{atividade.data}}</span>
                                                </p>
                                            </v-card-text>
                                            <br><br><br>
                                        </v-card>
                                        <br><br><br>
                                    </v-flex>
                                    <v-flex xs4 v-if="atividade.chat && (estado == 'pendente' || estado == 'coordenador')">
                                        <v-card>
                                            <v-container fluid grid-list-md id="scroll-target" style="max-height: 400px" class="scroll-y">
                                                <div id="scrolled-content">
                                                    <v-layout row wrap v-scroll:#scroll-target="onScroll">
                                                        <v-list three-line>
                                                            {{atividade.chat.assunto}}
                                                            <template v-for="(mensagem, index) in mensagensDoChat">
                                                                <v-list-tile :key="index" avatar>
                                                                    <v-list-tile-avatar>
                                                                        <img v-if="mensagem.user.foto" width="30px" height="30px" v-bind:src="getUserPhoto(mensagem.user.foto)"/>
                                                                    </v-list-tile-avatar>
                                                                    <v-list-tile-content>
                                                                        <v-list-tile-title v-html="mensagem.user.nome"></v-list-tile-title>
                                                                        <v-list-tile-sub-title v-html="mensagem.mensagem"></v-list-tile-sub-title>
                                                                    </v-list-tile-content>
                                                                </v-list-tile>
                                                            </template>
                                                        </v-list>
                                                        <div id="#botton">
                                                            <v-textarea v-model="mensagemAEnviar" auto-grow box color="brown" label="mensagem" rows="1"></v-textarea>
                                                            <v-btn color="success" @click="enviarMensagem">enviar</v-btn>
                                                        </div>
                                                    </v-layout>
                                                </div>
                                            </v-container>
                                        </v-card>
                                    </v-flex>
                                    <br><br><br>
                                    <p>
                                        Testemunhos:
                                    </p>
                                </v-layout>
                            </v-flex>
                        </v-container>
                    </v-card>
                </v-flex>
            </v-layout>
        </v-app>
        <v-dialog v-model="notificacaoDialog" max-width="290">
            <v-card>
                <v-card-title class="headline">Notificação para todos os alunos que estão com a atividade pendente</v-card-title>
                <v-textarea
                        v-model="mensagemNotificacao"
                        label="mensagem"
                ></v-textarea>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="red darken-1" flat="flat" @click="notificacaoDialog = false">
                        Cancelar
                    </v-btn>
                    <v-btn color="green darken-1" flat="flat" :disabled="!mensagemNotificacao" @click="enviarNotificacao()">Enviar</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
        <div class="modal fade" id="mostrarPatrimoniosModal" tabindex="-1" role="dialog" aria-labelledby="mostrarPatrimoniosModal"
             aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="container box">
                        <div class="modal-header">
                            <h5 class="modal-title">Patrimónios envolvidos na atividade</h5>
                            <button type="button" class="close" data-dismiss="modal"
                                    aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <v-list class="form-group" subheader>
                            <v-list-tile v-if="atividade.patrimonios"
                                    v-for="patrimonio in atividade.patrimonios" :key="patrimonio.id" avatar
                            >
                                <v-list-tile-avatar>
                                    <img v-if="patrimonio.imagens && patrimonio.imagens[0]" :src="getPatrimonioPhoto(patrimonio.imagens[0])">
                                    <span v-else>{{patrimonio.nome[0]}}</span>
                                </v-list-tile-avatar>
                                <v-list-tile-content>
                                    <v-list-tile-title>{{patrimonio.nome}}</v-list-tile-title>
                                </v-list-tile-content>
                            </v-list-tile>
                        </v-list>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>
<script type="text/javascript">
    export default {
        props: ['id'],
        data: function () {
            return {
                atividade: {},
                estado: '',
                mensagensDoChat: [],
                offset: 20,
                mensagemAEnviar: '',

                mensagemNotificacao: '',
                notificacaoDialog: false,
            };
        },
        methods: {
            getAtividade() {
                axios.get('/api/atividades/' + this.id)
                    .then(response => {
                        this.atividade = response.data.data;
                        if (response.data.estado && response.data.estado.estado){
                            this.estado = response.data.estado.estado;
                        } else if (response.data.estado !== undefined){
                            this.estado = null;
                        } else{
                            this.estado = 'outros';//professores e admins
                        }
                        if (this.atividade.coordenador.id === this.$store.state.user.id){
                            this.estado = 'coordenador';
                        }
                        if (this.atividade.chat && this.estado){
                            let mensagens = this.atividade.chat.chat_mensagens;
                            this.mensagensDoChat = mensagens.slice(mensagens.length - this.offset);
                            window.addEventListener("load", function(event) {//quando a página estiver carregada
                                document.getElementById("scroll-target").scrollTop =
                                    Math.floor(document.getElementById("scrolled-content").offsetHeight);
                            });
                            this.$socket.emit('user_enter', this.$store.state.user, this.atividade.chat.id);
                        }
                    })
                    .catch(errors => {
                        console.log(errors);
                    });
            },
            showPatrimonios(){
                $('#mostrarPatrimoniosModal').modal('show');
            },
            enviarNotificacao(){
                this.notificacaoDialog = false;
                axios.post('/api/notificacoes', {'atividade_id': this.id, 'mensagem': this.mensagemNotificacao}).then(response => {
                    this.toastPopUp("success", "Notificação enviada com sucesso!");
                    this.mensagemNotificacao = '';
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                });
            },
            participar(){
                axios.post('/api/atividades/' + this.id + '/participar', {'user_id': this.$store.state.user.id}).then(response => {
                    this.estado = 'pendente';
                    if (this.atividade.chat && this.estado){
                        this.$socket.emit('user_enter', this.$store.state.user, this.atividade.chat.id);
                    }
                    this.toastPopUp("success", "A atividade encontra-se na sua lista de atividades pendentes!");
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                })
            },
            enviarMensagem(){
                let chatMensagem = {
                    'chat_id': this.atividade.chat.id,
                    'mensagem': this.mensagemAEnviar,
                    'user_id': this.$store.state.user.id
                };
                axios.post('/api/chat', chatMensagem).then(response => {
                    this.$socket.emit('chat_mensagem', response.data, this.atividade.chat.id);
                    this.mensagemAEnviar = '';
                }).catch(error => {
                    this.toastPopUp("error", `${error.response.data.message}`);
                })
            },
            onScroll (e) {
                if (e.target.scrollTop == 0 && this.atividade.chat.chat_mensagens.length !== this.mensagensDoChat.length){
                    this.offset += 20;
                    this.toastPopUp("success", "Mais mensagens carregadas");
                    let mensagens = this.atividade.chat.chat_mensagens;
                    this.mensagensDoChat = mensagens.slice(mensagens.length - this.offset);
                }
            }
        },
        mounted() {
            this.getAtividade();
        },
        sockets: {
            novaMensagem(mensagem){
                this.atividade.chat.chat_mensagens.push(mensagem);
                this.mensagensDoChat.push(mensagem);
                document.getElementById("scroll-target").scrollTop =
                    Math.floor(document.getElementById("scrolled-content").offsetHeight);
            }
        },
        destroyed: function () {
            if (this.atividade.chat && this.estado && this.$store.state.user) {
                this.$socket.emit('user_exit', this.$store.state.user, this.atividade.chat.id);
            }
        },
    }
</script>

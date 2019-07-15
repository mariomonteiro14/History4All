<template>
    <div id="app">
        <br><br><br><br>
        <div v-if="isLoading">
            <h5 class="blue--text">
                <v-icon class="blue--text">far fa-comments</v-icon>
                &nbsp Chat privado para os professores
            </h5>
            <br><br><br>
            <v-progress-linear v-slot:progress :color="colorDefault"
                               indeterminate></v-progress-linear>
            <br><br><br><br><br><br><br><br><br>
        </div>
        <v-app v-else id="inspire">
            <v-container>
                <v-card flat>
                    <v-card-text>
                        <h5 class="blue--text">
                            <v-icon class="blue--text">far fa-comments</v-icon>
                            &nbsp Chat privado para os professores
                        </h5>
                        <div>
                            <v-divider></v-divider>
                            <span class=" font-weight-light grey--text">Assunto:</span>
                            <h6>{{chat.assunto}}</h6>
                        </div>
                        <v-divider></v-divider>
                        <v-flex xs12 id="scroll-target"
                                style="max-height:350px; min-height:250px; overflow-y:auto; overflow-x: hidden">
                            <v-layout row wrap v-scroll:#scroll-target="onScroll">
                                <div id="scrolled-content">
                                    <v-list three-line>
                                    <span class="grey--text"
                                          v-if="mensagensMostradas.length == 0">(Não existem mensagens)</span>
                                        <template v-for="(mensagem, index) in mensagensMostradas">
                                            <v-list-tile :key="index" avatar>
                                                <v-list-tile-avatar>
                                                    <img v-if="mensagem.user.foto" width="30px"
                                                         height="30px"
                                                         v-bind:src="getUserPhoto(mensagem.user.foto)"/>
                                                    <v-icon v-else class="indigo--text" small>
                                                        far fa-user
                                                    </v-icon>
                                                </v-list-tile-avatar>
                                                <v-list-tile-content>
                                                    <v-list-tile-title
                                                        v-html="mensagem.user.nome"></v-list-tile-title>
                                                    <v-list-tile-sub-title
                                                        v-html="mensagem.mensagem"></v-list-tile-sub-title>
                                                </v-list-tile-content>
                                            </v-list-tile>
                                        </template>
                                    </v-list>
                                </div>
                            </v-layout>
                        </v-flex>
                    </v-card-text>
                    <v-card-actions>
                        <v-layout row wrap>

                            <v-flex xs11>
                                <v-text-field
                                    v-model="mensagemAEnviar"
                                    outline
                                    clearable
                                    label="Mensagem"
                                    type="text"
                                    @click:clear="mensagemAEnviar=''"
                                    @keyup.enter="enviarMensagem"
                                    :disabled="aEnviarMensagem"
                                ></v-text-field>
                            </v-flex>
                            <v-spacer></v-spacer>
                            <v-flex xs1>
                                <v-btn  icon large v-if="mensagemAEnviar != '' && !aEnviarMensagem"
                                       @click="enviarMensagem">
                                    <v-icon class="blue--text" large>send</v-icon>
                                </v-btn>
                                <loader v-if="aEnviarMensagem" color="blue" size="36px"></loader>
                            </v-flex>

                        </v-layout>
                    </v-card-actions>
                </v-card>
            </v-container>
        </v-app>
    </div>
</template>
<script type="text/javascript">

    export default {
        data: function () {
            return {
                chat: {},
                mensagensMostradas: [],
                offset: 4,
                mensagemAEnviar: '',
                aEnviarMensagem: false,
                isLoading: true,
                actualScroll: 0,
            };
        },
        methods: {
            getChat() {
                this.isLoading = true;
                axios.get('/api/chat/professores')
                    .then(response => {
                        this.chat = response.data.data;
                        console.log(this.chat);
                        let mensagens = this.chat.chat_mensagens;
                        this.mensagensMostradas = mensagens.slice(mensagens.length - this.offset);
                        this.$socket.emit('user_enter_chat', this.$store.state.user, 1);
                        this.isLoading = false;
                    }).catch(error => {
                    this.isLoading = false;
                    this.toastErrorApi(error);
                });
            },
            enviarMensagem() {
                let chatMensagem = {
                    'chat_id': this.chat.id,
                    'mensagem': this.mensagemAEnviar,
                    'user_id': this.$store.state.user.id
                };
                this.aEnviarMensagem = true;
                axios.post('/api/chat', chatMensagem).then(response => {
                    this.$socket.emit('chat_mensagem', response.data, 1);
                    this.mensagemAEnviar = '';
                    this.aEnviarMensagem = false;
                }).catch(error => {
                    this.toastErrorApi(error);
                    this.aEnviarMensagem = false;
                })
            },
            onScroll(e) {
                if (this.actualScroll < e.target.scrollTop) {
                    this.scrollDown();
                }
                if (e && e.target.scrollTop === 0 && this.chat.chat_mensagens.length !== this.mensagensMostradas.length) {
                    this.offset += 20;
                    this.toastPopUp("success", "Mais mensagens carregadas");
                    let mensagens = this.chat.chat_mensagens;
                    this.mensagensMostradas = mensagens.slice(mensagens.length - this.offset);
                }
                this.actualScroll = e.target.scrollTop;
            },
            scrollDown() {
                document.getElementById("scroll-target").scrollTop = document.getElementById("scrolled-content").offsetHeight;
            }
        },
        mounted() {
            this.getChat();
        },
        sockets: {
            connect() {
                if (this.$store.state.user) {
                    this.$socket.emit('user_enter_chat', this.$store.state.user, 1);
                }
            },
            novaMensagem(mensagem) {
                this.chat.chat_mensagens.push(mensagem);
                this.mensagensMostradas.push(mensagem);
                this.scrollDown();
            }
        },
        destroyed: function () {
            if (this.$store.state.user) {
                this.$socket.emit('user_exit_chat', this.$store.state.user, 1);
            }
        },
    }
</script>


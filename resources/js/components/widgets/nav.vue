<template>
    <div>
        <v-toolbar :color="colorDefault" fixed>

            <!--</v-toolbar-items>-->
            <v-toolbar-items>
                <v-btn to="/" flat>
                    <v-img :src="'/Logo_preto.png'" alt="'HISTORY4ALL'"></v-img>
                </v-btn>
                <v-btn flat to="/patrimonios">Patrimonios</v-btn>
                <v-btn to="/atividades" v-if="this.$store.state.user" flat>Atividades</v-btn>
            </v-toolbar-items>
            <v-spacer></v-spacer>
            <!--<v-text-field
                flat
                solo-inverted
                label="Pesquisar"
                class="hidden-sm-and-down align-center"
                color="white"
                clearable
            >
            </v-text-field>-->
            <v-spacer></v-spacer>
            <b-dropdown v-if="this.$store.state.user && $store.state.user.tipo === 'admin'" text="Outline Danger" right
                        variant="Success" class="m-2">
                <template slot="button-content">GESTÃO</template>
                <b-dropdown-item to="/admin/patrimonios">
                    <v-icon> &nbsp build</v-icon>
                    Gerir Patrimónios
                </b-dropdown-item>
                <b-dropdown-item to="/admin/users">
                    <v-icon>group</v-icon>
                    &nbsp Gerir Utilizadores
                </b-dropdown-item>
                <b-dropdown-item to="/admin/escolas">
                    <v-icon>home</v-icon>
                    &nbsp Gerir Escolas | Turmas
                </b-dropdown-item>

            </b-dropdown>
            <b-dropdown v-if="this.$store.state.user && $store.state.user.tipo === 'professor'" text="Outline Danger"
                        right
                        variant="Success" class="m-2">
                <template slot="button-content">Minha Escola</template>
                <b-dropdown-item @click="$router.push({ name: 'professorGestor', params: {tab: 'escola' }})">
                    <v-icon> &nbsp home</v-icon>
                    Minha Escola
                </b-dropdown-item>
                <b-dropdown-item @click="$router.push({ name: 'professorGestor', params: {tab: 'turmas' }})">
                    <v-icon>group</v-icon>
                    &nbsp Gerir Turmas | Alunos
                </b-dropdown-item>
                <b-dropdown-item @click="$router.push({ name: 'professorGestor', params: {tab: 'professores' }})">
                    <v-icon>far fa-comments</v-icon>
                    &nbsp Professores | Chat
                </b-dropdown-item>
            </b-dropdown>
            <v-btn v-if="this.$store.state.user && $store.state.user.tipo === 'aluno'"
                   flat to="/alunos/escola">
                Minha Escola | Turma
            </v-btn>

            <v-toolbar-items v-if="!this.$store.state.user">
                <v-btn v-if="!isLoading" flat data-toggle="modal" data-target="#loginModal">
                    Entrar
                </v-btn>
                <v-layout v-else align-center fluid justify-left>
                    <loader :color="loader_color" :size="loader_size"></loader>
                </v-layout>
            </v-toolbar-items>
            <div v-else>
                <v-menu offset-y origin="center left" nudge-left class="dropdown elelvation-1" :nudge-bottom="14"
                        transition="scale-transition" data-toggle="dropdown"
                        data-backdrop="static">
                    <v-btn icon flat slot="activator">
                        <v-badge color="red" overlap>
                            <span slot="badge">{{numNotificacoesNaoLidas}}</span>
                            <v-icon medium>notifications</v-icon>
                        </v-badge>
                    </v-btn>
                </v-menu>
                <div class="dropdown-menu dropdown-menu-right"
                     id="dropdownNotificacoes"
                     aria-labelledby="dropdownNotificacoes"
                     style="max-height:400px; max-width:75vh;">
                    <div style="width: 70vh;">
                        <v-btn flat v-if="numNotificacoesNaoLidas > 0" @click="notificacoesLidas" color="blue">Marcar todas
                            como lidas
                        </v-btn>
                        <v-data-table v-show="this.$store.state.user"
                                      :items="notificacoes"
                                      class="elevation-1"
                                      hide-actions
                                      hide-headers disable-initial-sort
                                      style="max-height:400px; max-width:70vh; overflow-y:auto"

                        >
                            <template slot="no-data">
                                Não tem notificações
                            </template>
                            <template v-slot:items="props"
                                      style="width: 100%; display: inline-block; able-layout: fixed;">
                                <tr :style="[props.item.lida === 0 ? {'backgroundColor' : 'DarkSalmon'} : {}]">
                                    <td style=" overflow: hidden; display: inline; white-space: normal;">
                                        <span>{{props.item.remetente}} - {{formatarData(props.item.data)}}</span>
                                        <v-spacer></v-spacer>
                                        <div style="padding-left: 20px">
                                            <strong>
                                                {{props.item.mensagem}}
                                            </strong>
                                        </div>
                                    </td>
                                    <td class="text-xs-center">
                                        <v-tooltip bottom>
                                            <template v-slot:activator="{ on }">
                                                <v-icon v-on="on" class="material-icons"
                                                        v-if="props.item.lida === 0"
                                                        size="21px" @click="marcarNotificacaoLida(props.item)">
                                                    &#9711
                                                </v-icon>
                                                <v-icon v-else v-on="on"
                                                        @click="marcarNotificacaoNaoLida(props.item)"
                                                        size="25px">
                                                    check_circle
                                                </v-icon>
                                            </template>
                                            <span v-if="props.item.lida === 0">marcar como lida</span>
                                            <span v-else>marcar como não lida</span>
                                        </v-tooltip>
                                        <v-spacer></v-spacer>
                                        <v-spacer></v-spacer>

                                        <v-tooltip bottom v-if="props.item.link">
                                            <template v-slot:activator="{ on }">
                                                <v-btn small icon flat
                                                       @click="pushNotificacaoLink(props.item.link)">
                                                    <v-icon v-on="on" @click="pushNotificacaoLink(props.item.link)">
                                                        link
                                                    </v-icon>
                                                </v-btn>
                                            </template>
                                            <span>hiperligação para {{props.item.link}}</span>
                                        </v-tooltip>

                                    </td>

                                </tr>
                            </template>
                        </v-data-table>
                    </div>
                </div>
                <!--AVATAR-->
                <b-dropdown v-if="this.$store.state.user" right variant="Success" class="m-2" no-caret>
                    <template slot="button-content">
                        <v-btn icon large flat slot="activator">
                            <v-avatar size="30px" class="bg-white">
                                <v-img v-if="this.$store.state.user.foto"
                                       v-bind:src="getUserPhoto(this.$store.state.user.foto)">
                                    <template v-slot:placeholder>
                                        <v-layout
                                            fill-height
                                            align-center
                                            justify-center
                                            ma-0
                                        >
                                            <v-progress-circular indeterminate
                                                                 color="grey lighten-5"></v-progress-circular>
                                        </v-layout>
                                    </template>
                                </v-img>
                                <v-icon v-else class="indigo--text darken-4" small>far fa-user</v-icon>
                            </v-avatar>
                        </v-btn>
                    </template>
                    <v-list class="pa-0" absolute>
                        <v-list-tile ripple="ripple" rel="noopener" to="/me/perfil">
                            <v-list-tile-action>
                                <v-icon>account_circle</v-icon>
                            </v-list-tile-action>
                            <v-list-tile-content>
                                <v-list-tile-title>Perfil</v-list-tile-title>
                            </v-list-tile-content>
                        </v-list-tile>
                        <!--<v-list-tile ripple="ripple" rel="noopener" to="/professores/chat"
                                     v-if="$store.state.user.tipo === 'professor'">
                            <v-list-tile-action>
                                <v-icon>far fa-comments</v-icon>
                            </v-list-tile-action>
                            <v-list-tile-content>
                                <v-list-tile-title>Chat</v-list-tile-title>
                            </v-list-tile-content>
                        </v-list-tile>-->
                        <v-list-tile ripple="ripple" rel="noopener" @click="logout">
                            <v-list-tile-action>
                                <v-icon>fullscreen_exit</v-icon>
                            </v-list-tile-action>
                            <v-list-tile-content>
                                <v-list-tile-title>Sair</v-list-tile-title>
                            </v-list-tile-content>
                        </v-list-tile>
                    </v-list>
                </b-dropdown>
            </div>
        </v-toolbar>

        <!-- Login Modal -->
        <login v-on:logging="logging" v-on:logged="getNotificacoes()"></login>
    </div>
</template>
<script>

    import login from './login.vue';
    import BContainer from "bootstrap-vue/src/components/layout/container";

    export default {
        name: 'app-toolbar',
        components: {
            BContainer,
            'login': login,
        },
        data: () => ({
            isLoading: false,
            loader_color: '#ffffff',
            loader_size: '30px',
            notificacoes: [],
            numNotificacoesNaoLidas: 0
        }),
        computed: {
            toolbarColor() {
                return this.$vuetify.options.extra.mainNav;
            },

        },
        methods: {
            handleDrawerToggle() {
                window.getApp.$emit('APP_DRAWER_TOGGLED');
            },
            formatarData(data) {
                let aux = new Date(data.replace(/-/g, "/"));
                let m = new Date();
                if (aux.getFullYear() == m.getFullYear() && aux.getMonth() == m.getMonth()) {
                    if (aux.getDate() == m.getDate()) {
                        if (aux.getHours() == m.getHours()) {
                            if ((m.getMinutes() - aux.getMinutes()) <= 1) {
                                return "Agora"
                            }
                            return "Há " + (m.getMinutes() - aux.getMinutes()) + " minutos";
                        }
                        let difHoras = m.getHours() - aux.getHours();
                        if (difHoras == 1) {
                            return "Há 1 hora"
                        }
                        if (difHoras <= 12) {
                            return "Há " + difHoras + " horas";
                        }
                        return "Hoje";
                    }
                    if ((m.getDate() - aux.getDate()) == 1) {
                        return "Ontem";
                    }
                }
                return aux.getDate() + "/" + (aux.getMonth() + 1) + "/" + aux.getFullYear(); //janeiro começa em 0
            },
            logout() {
                this.logging();
                axios.get('/api/logout').then(response => {
                    this.logging();
                    this.$store.commit('clearUserAndToken');
                    this.toastPopUp("success", "Saiu da sua conta com sucesso!");
                    this.notificacoes = [];
                    this.$socket.emit('user_exit', this.$store.state.user);
                    if (!(this.$route.name == "patrimoniosShow" || this.$route.name == "patrimonios" ||
                        this.$route.name == "forums" || this.$route.name == "showForum" || this.$route.name == "index")) {
                        this.$router.push({name: 'index'});
                    }
                }).catch(error => {
                    this.$store.commit('clearUserAndToken');
                    this.toastPopUp("error", "Erro ao sair");
                    this.logging();
                });

            },
            logging() {
                this.isLoading = !this.isLoading;
            },
            notificacoesLidas() {
                if (this.numNotificacoesNaoLidas > 0) {
                    axios.put('/api/me/notificacoes').then(response => {
                        this.notificacoes = response.data.data;
                        this.numNotificacoesNaoLidas = 0;
                    }).catch(error => {
                        this.toastErrorApi(error);
                    });
                }
            },
            getNotificacoes() {
                if (!this.$store.state.user) {
                    return;
                }
                axios.get('/api/me/notificacoes')
                    .then(response => {
                        this.notificacoes = response.data.data;
                        this.numNotificacoesNaoLidas =
                            this.notificacoes.filter(notificacao => notificacao.lida === 0).length;
                    }).catch(error => {
                    this.toastErrorApi(error);
                    this.isLoading = false;
                });
            },
            pushNotificacaoLink(link) {
                if (link) {
                    '/' + link === this.$route.path ? this.toastPopUp('show', 'Já se encontra na página') : this.$router.push(link);
                }
            },
            //colocar notificaçao como lida
            marcarNotificacaoLida(notificacao) {
                this.numNotificacoesNaoLidas--;
                notificacao.lida = 1;
                axios.put('/api/me/notificacoes/' + notificacao.id + '/lida').then(response => {
                }).catch(error => {
                    this.toastErrorApi(error);
                    this.numNotificacoesNaoLidas++;
                    notificacao.lida = 0;
                });
            },
            //colocar notificaçao como nao lida
            marcarNotificacaoNaoLida(notificacao) {
                this.numNotificacoesNaoLidas++;
                notificacao.lida = 0;
                axios.put('/api/me/notificacoes/' + notificacao.id + '/naolida').then(response => {
                }).catch(error => {
                    this.toastErrorApi(error);
                    this.numNotificacoesNaoLidas--;
                    notificacao.lida = 1;
                });
            },
        },
        mounted() {
            if (this.$store.state.user) {
                this.getNotificacoes();
            }
            $('#dropdownNotificacoes').on('click', function (event) {
                event.stopPropagation();
            });
        },
        sockets: {
            novaNotificacao(mensagem) {
                this.notificacoes.unshift(mensagem);
                this.numNotificacoesNaoLidas++;
            },
            atualizarNotificacoes() {
                this.getNotificacoes();
            }
        }
    };
</script>
<style>
    .modal-open {
        overflow: hidden;
        position: fixed;
        width: 100%;
        height: 100%;
        margin-right: 0px !important;
    }

    body.with-modal {
        position: static;
        height: auto;
        overflow-y: hidden;
    }
</style>


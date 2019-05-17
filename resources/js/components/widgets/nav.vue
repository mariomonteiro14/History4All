<template>
        <div>
            <v-toolbar :color="colorDefault" fixed>

            <v-btn to="/">
                HISTORY4ALL
            </v-btn>
        <!--</v-toolbar-items>-->
        <v-toolbar-items>
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
            <b-dropdown v-if="this.$store.state.user && $store.state.user.tipo !== 'aluno'" text="Outline Danger" right variant="Success" class="m-2">
                <template slot="button-content">GESTÃO</template>
                <div v-if="$store.state.user.tipo === 'admin'">
                    <b-dropdown-item to="/admin/patrimonios"><v-icon> &nbsp build</v-icon> Gerir Patrimónios</b-dropdown-item>
                    <b-dropdown-item to="/admin/users"><v-icon>group</v-icon> &nbsp Gerir Utilizadores</b-dropdown-item>
                    <b-dropdown-item to="/admin/escolas"><v-icon>home</v-icon> &nbsp Gerir Escolas | Turmas</b-dropdown-item>
                </div>
                <div v-if="$store.state.user.tipo === 'professor'">
                    <b-dropdown-item to="/escola/turmas"><v-icon>group</v-icon>&nbsp Turmas</b-dropdown-item>
                </div>
            </b-dropdown>
        <v-toolbar-items v-if="!this.$store.state.user">
            <v-btn v-if="!isLoading" flat data-toggle="modal" data-target="#loginModal">
                Login
            </v-btn>
            <v-layout v-else align-center  fluid justify-left>
                <loader :color="loader_color" :size="loader_size"></loader>
            </v-layout>
        </v-toolbar-items>
        <div v-else>
            <v-menu offset-y origin="center left" nudge-left class="dropdown elelvation-1" :nudge-bottom="14" transition="scale-transition" data-toggle="dropdown">
                <v-btn icon flat slot="activator" @click="notificacoesLidas">
                    <v-badge color="red" overlap>
                        <span slot="badge">{{novasNotificacoes}}</span>
                        <v-icon medium>notifications</v-icon>
                    </v-badge>
                </v-btn>
            </v-menu>
                <div v-show="this.$store.state.user" class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
                    <div class="p-4" style="width: 80vh">
                        <div class="container-fluid">
                            <template v-for="(notificacao, index) in notificacoes">
                                <v-list-tile :key="index" avatar>
                                    <v-list-tile-content>
                                        <v-list-tile-title v-text="notificacao.de"></v-list-tile-title>
                                        <v-list-tile-title v-text="notificacao.data"></v-list-tile-title>
                                        <v-list-tile-title v-text="notificacao.mensagem"></v-list-tile-title>
                                    </v-list-tile-content>
                                </v-list-tile>
                            </template>
                        </div>
                    </div>
                </div>
            <!--AVATAR-->
            <b-dropdown v-if="this.$store.state.user" right variant="Success" class="m-2" no-caret>
                <template slot="button-content" >
                    <v-btn icon large flat slot="activator">
                        <v-avatar size="30px" class="bg-white">
                            <img v-if="this.$store.state.user.foto" v-bind:src="getUserPhoto(this.$store.state.user.foto)"/>
                            <v-icon v-else class="indigo--text darken-4" small>far fa-user</v-icon>
                        </v-avatar>
                    </v-btn>
                </template>
                <v-list class="pa-0" absolute>
                    <v-list-tile ripple="ripple" rel="noopener" to="/me/perfil">
                        <v-list-tile-action >
                            <v-icon>account_circle</v-icon>
                        </v-list-tile-action>
                        <v-list-tile-content>
                            <v-list-tile-title>Perfil</v-list-tile-title>
                        </v-list-tile-content>
                    </v-list-tile>
                    <v-list-tile ripple="ripple" rel="noopener" @click="logout">
                        <v-list-tile-action >
                            <v-icon>fullscreen_exit</v-icon>
                        </v-list-tile-action>
                        <v-list-tile-content>
                            <v-list-tile-title>Logout</v-list-tile-title>
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
            loader_size:'30px',
            notificacoes: [],
            novasNotificacoes: 0
        }),
        computed: {
            toolbarColor () {
                return this.$vuetify.options.extra.mainNav;
            },
        },
        methods: {
            handleDrawerToggle () {
                window.getApp.$emit('APP_DRAWER_TOGGLED');
            },
            logout() {
                this.logging();
                axios.get('/api/logout').then(response => {
                    this.logging();
                    this.$store.commit('clearUserAndToken');
                    this.toastPopUp("success", "Logged out");
                    this.$socket.emit('user_exit', this.$store.state.user);
                    this.$router.push({name: 'index'});
                }).catch(error => {
                    this.$store.commit('clearUserAndToken');
                    this.toastPopUp("error", "Error on logout");
                    console.log(error);
                    this.logging();
                });

            },
            logging(){
                this.isLoading = !this.isLoading;
                this.getNotificacoes();
            },
            notificacoesLidas(){
                if (this.novasNotificacoes > 0){
                    axios.put('/api/me/notificacoes').then(response => {
                        this.notificacoes = response.data;
                        this.novasNotificacoes = 0;
                    }).catch(error => {
                        this.toastPopUp("error", `${error.response.data.message}`);
                    });
                }
            },
            getNotificacoes(){
                if (!this.$store.state.user){
                    return;
                }
                axios.get('/api/me/notificacoes')
                    .then(response => {
                        this.notificacoes = response.data.data;
                        this.novasNotificacoes =
                            this.notificacoes.filter(notificacao => notificacao.nova === 1).length;
                    })
                    .catch(errors => {
                        console.log(errors);
                        this.isLoading = false;
                    });
            }
        },
        mounted() {
            if (this.$store.state.user){
                this.getNotificacoes();
            }
        },
        sockets: {
            novaNotificacao(mensagem) {
                this.notificacoes.unshift(mensagem);
                this.novasNotificacoes++;
            },
            atualizarNotificacoes(){
                this.getNotificacoes();
            }
        }
    };
</script>
<style>
    .modal-open {
        overflow: hidden;
        position:fixed;
        width: 100%;
        height: 100%;
        margin-right:0px !important;
    }
    body.with-modal {
        position: static;
        height: auto;
        overflow-y: hidden;
    }
</style>


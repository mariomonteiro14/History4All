<template>
        <div>
            <v-toolbar color="brown" fixed>
                <!-- <v-toolbar-title class="ml-0 pl-3">
                 </v-toolbar-title>
                 <v-toolbar-side-icon @click.stop="handleDrawerToggle"></v-toolbar-side-icon>
                 <v-toolbar-items>
                 -->
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
                    <b-dropdown-item to="/admin/patrimonios"><i class="material-icons sm1">build</i> Gerir Patrimónios</b-dropdown-item>
                    <b-dropdown-item to="/admin/users"><i class="material-icons vsm-icon">group</i> Gerir Utilizadores</b-dropdown-item>
                    <b-dropdown-item to="/admin/escolas"><i class="material-icons">home</i> Gerir Escolas | Turmas</b-dropdown-item>
                </div>
                <div v-if="$store.state.user.tipo === 'professor'">
                    <b-dropdown-item to="/professor/atividades"><i class="material-icons sm1">build</i>Minhas Atividades</b-dropdown-item>
                    <b-dropdown-item to="/escola/turmas"><i class="material-icons vsm-icon">group</i>Minhas Turmas</b-dropdown-item>
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
            <v-menu offset-y origin="center left" nudge-left class="elelvation-1" :nudge-bottom="14" transition="scale-transition">
                <v-btn icon flat slot="activator">
                    <v-badge color="red" overlap>
                        <span slot="badge">3</span>
                        <v-icon medium>notifications</v-icon>
                    </v-badge>
                </v-btn>
            </v-menu>
            <!--AVATAR-->
            <b-dropdown v-if="this.$store.state.user" right variant="Success" class="m-2" no-caret>
                <template slot="button-content" >
                    <v-btn icon large flat slot="activator">
                        <v-avatar size="30px" class="bg-success">
                            <img v-if="this.$store.state.user.foto" v-bind:src="getUserPhoto(this.$store.state.user.foto)"/>
                            <span v-else>{{this.$store.state.user.nome[0]}}</span>
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
        <login v-on:logging="logging"></login>
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
            items: [
                {
                    icon: 'account_circle',
                    href: '#',
                    title: 'Profile',
                    click: (e) => {
                        console.log(e);
                    }
                },
                {
                    icon: 'settings',
                    href: '#',
                    title: 'Settings',
                    click: (e) => {
                        console.log(e);
                    }
                },
                {
                    icon: 'fullscreen_exit',
                    href: '#',
                    title: 'Logout',
                    click: () => {
                        this.methods.logout();
                    }
                }
            ],
            isLoading: false,
            loader_color: '#ffffff',
            loader_size:'30px',
        }),
        computed: {
            toolbarColor () {
                return this.$vuetify.options.extra.mainNav;
            }
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
            }
        }
    };
</script>
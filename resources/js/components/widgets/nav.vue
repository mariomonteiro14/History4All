<template>
        <div>
            <v-toolbar color="yellow" fixed>
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
            <v-btn v-if="this.$store.state.user" flat>Atividades</v-btn>
        </v-toolbar-items>
        <v-spacer></v-spacer>
        <v-text-field
            flat
            solo-inverted
            label="Search"
            class="hidden-sm-and-down align-center"
            color="white"
        >
        </v-text-field>
        <v-spacer></v-spacer>
                <v-toolbar-items v-if="this.$store.state.user">
                    <v-btn flat>Dashboard</v-btn>
                </v-toolbar-items>
        <v-toolbar-items v-if="!this.$store.state.user">
            <v-btn flat data-toggle="modal" data-target="#loginModal">
                Login
            </v-btn>
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
        <v-menu offset-y origin="center center" :nudge-bottom="10" transition="scale-transition">
            <v-btn icon large flat slot="activator">
                <v-avatar size="30px">
                    <img v-bind:src="getUserPhoto(this.$store.state.user.photo)" />
                </v-avatar>
            </v-btn>
            <v-list class="pa-0" absolute>
                <v-list-tile ripple="ripple" rel="noopener" @click="">
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
        </v-menu>
       </div>
    </v-toolbar>

        <!-- Login Modal -->
        <login></login>
    </div>
</template>
<script>
    import login from './login.vue';
    export default {
        name: 'app-toolbar',
        components: {
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
                axios.get('api/logout').then(response => {
                    this.$store.commit('clearUserAndToken');
                    this.toastPopUp("success", "Logged out");
                    this.$router.push({name: 'index'});
                }).catch(error => {
                    this.$store.commit('clearUserAndToken');
                    this.toastPopUp("error", "Error on logout");
                    console.log(error);
                })
            },
        }
    };
</script>

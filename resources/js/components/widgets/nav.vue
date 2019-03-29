<template>
    <div>
    <v-toolbar color="primary" light>
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
            <v-btn to="/patrimonios">
                Patrimonios
            </v-btn>
        </v-toolbar-items>
        <v-spacer></v-spacer>
        <v-text-field
            flat
            solo-inverted
            prepend-icon="search"
            label="Search"
            class="hidden-sm-and-down align-center"
        >
        </v-text-field>
        <v-spacer></v-spacer>
        <v-toolbar-items v-if="!this.$store.state.user">
            <v-btn flat data-toggle="modal" data-target="#loginModal">
                Login
            </v-btn>
        </v-toolbar-items>

       <div v-else>
        <v-menu offset-y origin="center center" class="elelvation-1" :nudge-bottom="14" transition="scale-transition">
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
            <v-list class="pa-0">
                <v-list-tile v-for="(item,index) in items" :to="!item.href ? { name: item.name } : null" :href="item.href" @click="item.click" ripple="ripple" :disabled="item.disabled" :target="item.target" rel="noopener" :key="index">
                    <v-list-tile-action v-if="item.icon">
                        <v-icon>{{ item.icon }}</v-icon>
                    </v-list-tile-action>
                    <v-list-tile-content>
                        <v-list-tile-title>{{ item.title }}</v-list-tile-title>
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
                        this.logout();
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

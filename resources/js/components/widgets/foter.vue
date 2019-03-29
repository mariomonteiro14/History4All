<template>
    <v-card>
        <v-footer height="auto" color="white">

            <v-layout justify-center row wrap>
              <!--  <v-btn
                    v-for="link in links"
                    :key="link"
                    color="white"
                    flat
                    round
                >
                    {{ link }}
                </v-btn>-->
                <v-flex
                    primary
                    lighten-2
                    py-3
                    text-xs-center
                    dark--text
                    xs6
                >
                    <strong >History4All - Projeto Informárico 2019</strong>
                </v-flex>
            </v-layout>
        </v-footer>
    </v-card>

</template>

<script>
    export default {
        data: () => ({
            links: [
                'Home',
                'About Us',
                'Team',
                'Services',
                'Blog',
                'Contact Us'
            ]
        })
    }
</script>

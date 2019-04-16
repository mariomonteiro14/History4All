<template>
    <div>

    </div>
</template>

<script>
    export default {
        props: ['token'],
        created() {
            this.getUser();
        },
        data: function () {
            return {
                user: {},
            };
        },
        methods: {
            getUser: function () {
                axios.get("api/users/token/" + this.token).then(response => {
                    if (response.data === ""){//token inválido
                        this.$router.push('/');
                    }
                    this.user = response.data;
                }).catch(errors => {
                    console.log(errors);
                });
            },
        },
    }
</script>

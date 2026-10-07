<template>
    <router-view></router-view>
</template>

<script>
export default {
    name: 'App',
    mounted() {
        console.log('App component mounted');
    }
};
</script>
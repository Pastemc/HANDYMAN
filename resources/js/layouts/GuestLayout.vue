<template>
    <div class="guest-layout">
        <router-view></router-view>
    </div>
</template>

<script>
export default {
    name: 'GuestLayout',
};
</script>

<style scoped>
.guest-layout {
    min-height: 100vh;
    width: 100%;
}
</style>

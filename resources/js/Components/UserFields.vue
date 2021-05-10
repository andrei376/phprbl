<template>
    <div>
        <breeze-label for="name" field="name" value="Name" />
        <breeze-input id="name" type="text" class="mt-1 block w-full" v-model="form.name" field="name" required autofocus autocomplete="off" />
    </div>

    <div class="mt-4">
        <breeze-label for="email" field="email" value="Email" />
        <breeze-input id="email" field="email" type="email" class="mt-1 block w-full" v-model="form.email" required autocomplete="username" />
    </div>

    <div class="mt-4">
        <breeze-label for="password" field="password" value="Password" />
        <breeze-input id="password" field="password" type="password" class="mt-1 block w-full" v-model="form.password" required autocomplete="new-password" />
    </div>

    <div class="mt-4">
        <breeze-label for="password_confirmation" field="password_confirmation" value="Confirm Password" />
        <breeze-input id="password_confirmation" field="password_confirmation" type="password" class="mt-1 block w-full" v-model="form.password_confirmation" required autocomplete="new-password" />
    </div>

</template>

<script>
    import BreezeInput from '@/Components/Input'
    import BreezeLabel from '@/Components/Label'
    import BreezeValidationErrors from '@/Components/ValidationErrors'

    export default {
        components: {
            BreezeInput,
            BreezeLabel,
            BreezeValidationErrors
        },

        inheritAttrs: false,

        props: {
            'form': Object
        }
    }
</script>


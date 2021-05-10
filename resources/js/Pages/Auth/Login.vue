<template>
    <breeze-validation-errors class="mb-4" />

    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
        {{ status }}
    </div>

    <form @submit.prevent="submit">
        <div>
            <breeze-label for="email" value="Email" />
            <breeze-input id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus autocomplete="username" />
        </div>

        <div class="mt-4">
            <breeze-label for="password" value="Password" />
            <breeze-input id="password" type="password" class="mt-1 block w-full" v-model="form.password" required autocomplete="current-password" />
        </div>

        <div class="block mt-4">
            <label class="flex items-center">
                <breeze-checkbox name="remember" v-model:checked="form.remember" />
                <span class="ml-2 text-sm text-gray-600">Remember me</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-4">
            <inertia-link v-if="canRegister" :href="this.route('register')" class="text-sm text-gray-600 underline hover:text-gray-900">
                {{ this.__('Register new account') }}
            </inertia-link>

            <inertia-link v-if="canResetPassword" :href="this.route('password.request')" class="underline text-sm text-gray-600 hover:text-gray-900">
                {{ this.__('Forgot your password?') }}
            </inertia-link>
        </div>

        <div class="flex items-center justify-end mt-4">
            <breeze-button class="ml-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ this.__('Log in') }}
            </breeze-button>
        </div>
    </form>
</template>

<script>
    import BreezeButton from '@/Components/Button'
    import BreezeGuestLayout from "@/Layouts/Guest"
    import BreezeInput from '@/Components/Input'
    import BreezeCheckbox from '@/Components/Checkbox'
    import BreezeLabel from '@/Components/Label'
    import BreezeValidationErrors from '@/Components/ValidationErrors'

    export default {
        layout: BreezeGuestLayout,

        components: {
            BreezeButton,
            BreezeInput,
            BreezeCheckbox,
            BreezeLabel,
            BreezeValidationErrors
        },

        props: {
            auth: Object,
            canResetPassword: Boolean,
            canRegister: Boolean,
            errors: Object,
            status: String,
        },

        data() {
            return {
                form: this.$inertia.form({
                    email: '',
                    password: '',
                    remember: false
                })
            }
        },

        methods: {
            submit() {
                //refresh csrf token
                axios.get(this.route('token')).then(function() {
                    this.form
                        .transform(data => ({
                            ... data,
                            remember: this.form.remember ? 'on' : ''
                        }))
                        .post(this.route('login'), {
                            onFinish: () => this.form.reset('password'),
                        })
                }.bind(this)).catch(() => {
                    alert('Error');
                });
            }
        }
    }
</script>

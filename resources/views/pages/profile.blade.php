@extends('layouts.app')

@php
    $profileRoutes = [
        'infoUpdate' => route('profile.info.update'),
        'addressUpdate' => route('profile.address.update'),
    ];
@endphp

@section('content')
    <x-common.page-breadcrumb pageTitle="Profile" />

    <div
        x-data="profilePage(@js($profile), @js($address), @js($socials), @js($profileRoutes), @js(csrf_token()))"
        x-effect="document.body.style.overflow = (isProfileInfoModal || isProfileAddressModal) ? 'hidden' : 'unset'"
        class="rounded-2xl border border-gray-200/50 bg-white/80 p-5 backdrop-blur-2xl transition-all duration-300 dark:border-white/5 dark:bg-gray-900/80 lg:p-6"
    >
        <h3 class="mb-5 bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-lg font-bold text-transparent dark:from-white dark:to-gray-400 lg:mb-7">Profile</h3>

        @if (session('status'))
            <div class="mb-5 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300">
                {{ session('status') }}
            </div>
        @endif

        <div
            id="profileMessage"
            x-show="message.text"
            x-cloak
            class="mb-5 rounded-xl border px-4 py-3 text-sm"
            :class="message.type === 'success'
                ? 'border-success-200 bg-success-50 text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-300'
                : (message.type === 'error'
                    ? 'border-error-200 bg-error-50 text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300'
                    : 'border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-900/70 dark:text-gray-300')"
        >
            <span x-text="message.text"></span>
        </div>

        <div class="mb-6 rounded-2xl border border-gray-200/50 bg-white/80 p-5 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80 lg:p-6">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex w-full flex-col items-center gap-6 xl:flex-row">
                    <div class="flex flex-col items-center gap-3">
                        <div class="h-20 w-20 overflow-hidden rounded-full border border-gray-200 dark:border-gray-800">
                            <img
                                data-profile-avatar
                                :src="profile.avatar"
                                alt="Profile photo"
                                class="h-full w-full object-cover object-top"
                            />
                        </div>

                        <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="flex flex-col items-center gap-2">
                            @csrf
                            <label
                                for="profile_avatar"
                                :class="isUploadingAvatar ? 'pointer-events-none opacity-70' : ''"
                                class="inline-flex cursor-pointer items-center justify-center rounded-full border border-gray-300 bg-white px-4 py-2 text-xs font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03] dark:hover:text-white"
                            >
                                <span x-show="!isUploadingAvatar">Upload photo</span>
                                <span x-show="isUploadingAvatar" x-cloak>Uploading...</span>
                            </label>
                            <input
                                id="profile_avatar"
                                name="avatar"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                class="hidden"
                                @change="if ($event.target.files.length) { isUploadingAvatar = true; $event.target.form.submit(); }"
                            />
                            <p class="text-center text-xs text-gray-500 dark:text-gray-400">JPG, PNG, or WEBP up to 2 MB.</p>
                            @error('avatar')
                                <p class="text-center text-xs text-error-600 dark:text-error-400">{{ $message }}</p>
                            @enderror
                        </form>
                    </div>

                    <div class="order-3 xl:order-2">
                        <h4
                            data-profile-full-name
                            class="mb-2 text-center text-lg font-semibold text-gray-800 dark:text-white/90 xl:text-left"
                            x-text="profile.fullName || 'Profile User'"
                        ></h4>
                        <div class="flex flex-col items-center gap-1 text-center xl:flex-row xl:gap-3 xl:text-left">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <span data-profile-role x-text="profile.role || 'Interview Practice Member'"></span>
                            </p>
                            <div class="hidden h-3.5 w-px bg-gray-300 dark:bg-gray-700 xl:block"></div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <span data-profile-location x-text="profile.location || 'Profile ready for customization'"></span>
                            </p>
                        </div>
                    </div>

                    <div class="order-2 flex grow items-center gap-2 xl:order-3 xl:justify-end">
                        @foreach (['facebook' => 'F', 'x' => 'X', 'linkedin' => 'in', 'instagram' => 'Ig'] as $key => $label)
                            <button
                                type="button"
                                data-profile-social-link="{{ $key }}"
                                @click="openSocial('{{ $key }}', '{{ ucfirst($key) }}')"
                                class="flex h-11 w-11 items-center justify-center rounded-full border border-gray-300 bg-white text-sm font-semibold text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.03] dark:hover:text-white"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <button
                    type="button"
                    @click="openProfileInfoModal()"
                    class="flex w-full items-center justify-center rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex lg:w-auto"
                >
                    Edit
                </button>
            </div>
        </div>

        <div class="mb-6 rounded-2xl border border-gray-200/50 bg-white/80 p-5 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80 lg:p-6">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mb-6">Personal Information</h4>
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Full Name</p>
                            <p data-profile-full-name class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="profile.fullName || 'Profile User'"></p>
                        </div>
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Role</p>
                            <p data-profile-role class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="profile.role || 'Interview Practice Member'"></p>
                        </div>
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Email address</p>
                            <p data-profile-email class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="profile.email"></p>
                        </div>
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Phone</p>
                            <p data-profile-phone class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="profile.phone || 'Add your phone number'"></p>
                        </div>
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Location</p>
                            <p data-profile-location class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="profile.location || 'Profile ready for customization'"></p>
                        </div>
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Bio</p>
                            <p data-profile-bio class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="profile.bio || 'Focused on interview practice, programming, and continuous improvement.'"></p>
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    @click="openProfileInfoModal()"
                    class="flex w-full items-center justify-center rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex lg:w-auto"
                >
                    Edit
                </button>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200/50 bg-white/80 p-5 shadow-theme-xs backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-theme-md dark:border-white/5 dark:bg-gray-900/80 lg:p-6">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mb-6">Address</h4>
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Country</p>
                            <p data-profile-country class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="address.country || 'Not set'"></p>
                        </div>
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">City/State</p>
                            <p data-profile-city-state class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="address.cityState || 'Update your city and province'"></p>
                        </div>
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Postal Code</p>
                            <p data-profile-postal-code class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="address.postalCode || 'Not set'"></p>
                        </div>
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">National ID / PhilSys ID</p>
                            <p data-profile-tax-id class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="address.taxId || 'Not set'"></p>
                        </div>
                    </div>
                </div>

                <button
                    type="button"
                    @click="openProfileAddressModal()"
                    class="flex w-full items-center justify-center rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200 lg:inline-flex lg:w-auto"
                >
                    Edit
                </button>
            </div>
        </div>

        <div x-show="isProfileInfoModal" x-cloak x-transition.opacity class="fixed inset-0 z-[99999] flex items-center justify-center p-5">
            <div @click="isProfileInfoModal = false" class="fixed inset-0 bg-gray-400/50 backdrop-blur-[24px]"></div>
            <div @click.stop x-transition.scale class="relative w-full max-w-[700px] rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
                <button type="button" @click="isProfileInfoModal = false" class="absolute right-3 top-3 flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-xl text-gray-500 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300">&times;</button>
                <div class="px-2 pr-14">
                    <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Edit Personal Information</h4>
                    <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">Update your details to keep your profile up-to-date.</p>
                </div>

                <form class="flex flex-col" @submit.prevent="saveProfileInfo()">
                    <div class="custom-scrollbar h-[458px] overflow-y-auto p-2">
                        <h5 class="mb-5 text-lg font-medium text-gray-800 dark:text-white/90 lg:mb-6">Social Links</h5>
                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                            <input x-model.trim="draftSocials.facebook" type="text" placeholder="Facebook URL" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                            <input x-model.trim="draftSocials.x" type="text" placeholder="X URL" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                            <input x-model.trim="draftSocials.linkedin" type="text" placeholder="LinkedIn URL" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                            <input x-model.trim="draftSocials.instagram" type="text" placeholder="Instagram URL" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>

                        <h5 class="mb-5 mt-7 text-lg font-medium text-gray-800 dark:text-white/90 lg:mb-6">Personal Information</h5>
                        <div class="grid grid-cols-1 gap-x-6 gap-y-5 lg:grid-cols-2">
                            <input x-model.trim="draftProfile.fullName" type="text" placeholder="Full Name" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 lg:col-span-2" />
                            <input x-model.trim="draftProfile.email" type="email" placeholder="Email Address" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                            <input x-model.trim="draftProfile.phone" type="text" placeholder="Phone" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                            <input x-model.trim="draftProfile.role" type="text" placeholder="Role" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                            <input x-model.trim="draftProfile.location" type="text" placeholder="Location" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                            <textarea x-model.trim="draftProfile.bio" rows="4" placeholder="Bio" class="dark:bg-dark-900 lg:col-span-2 w-full rounded-2xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center gap-3 px-2 lg:justify-end">
                        <button @click="isProfileInfoModal = false" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto">Close</button>
                        <button type="submit" :disabled="isSavingProfileInfo" :class="isSavingProfileInfo ? 'cursor-not-allowed opacity-70' : ''" class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                            <span x-show="!isSavingProfileInfo">Save Changes</span>
                            <span x-show="isSavingProfileInfo" x-cloak>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="isProfileAddressModal" x-cloak x-transition.opacity class="fixed inset-0 z-[99999] flex items-center justify-center p-5">
            <div @click="isProfileAddressModal = false" class="fixed inset-0 bg-gray-400/50 backdrop-blur-[24px]"></div>
            <div @click.stop x-transition.scale class="relative w-full max-w-[700px] rounded-3xl bg-white p-4 dark:bg-gray-900 lg:p-11">
                <button type="button" @click="isProfileAddressModal = false" class="absolute right-3 top-3 flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-xl text-gray-500 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300">&times;</button>
                <div class="px-2 pr-14">
                    <h4 class="mb-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Edit Address</h4>
                    <p class="mb-6 text-sm text-gray-500 dark:text-gray-400 lg:mb-7">Update your address details to keep your profile up-to-date.</p>
                </div>

                <form class="flex flex-col" @submit.prevent="saveProfileAddress()">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-5 px-2 lg:grid-cols-2">
                        <input x-model.trim="draftAddress.country" type="text" placeholder="Country" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        <input x-model.trim="draftAddress.cityState" type="text" placeholder="City/State" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        <input x-model.trim="draftAddress.postalCode" type="text" placeholder="Postal Code" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        <input x-model.trim="draftAddress.taxId" type="text" placeholder="National ID / PhilSys ID" class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div class="mt-6 flex items-center gap-3 px-2 lg:justify-end">
                        <button @click="isProfileAddressModal = false" type="button" class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 sm:w-auto">Close</button>
                        <button type="submit" :disabled="isSavingAddress" :class="isSavingAddress ? 'cursor-not-allowed opacity-70' : ''" class="flex w-full justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 sm:w-auto">
                            <span x-show="!isSavingAddress">Save Changes</span>
                            <span x-show="isSavingAddress" x-cloak>Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>[x-cloak]{display:none!important;}</style>
@endsection

@push('scripts')
    <script>
        function profilePage(initialProfile, initialAddress, initialSocials, routes, csrfToken) {
            return {
                profile: initialProfile,
                address: initialAddress,
                socials: initialSocials,
                routes,
                csrfToken,
                draftProfile: {},
                draftAddress: {},
                draftSocials: {},
                isProfileInfoModal: false,
                isProfileAddressModal: false,
                isUploadingAvatar: false,
                isSavingProfileInfo: false,
                isSavingAddress: false,
                message: { type: '', text: '' },
                messageTimeout: null,
                init() {
                    this.draftProfile = this.clone(this.profile);
                    this.draftAddress = this.clone(this.address);
                    this.draftSocials = this.clone(this.socials);
                },
                clone(value) {
                    return JSON.parse(JSON.stringify(value));
                },
                showMessage(type, text) {
                    this.message = { type, text };
                    clearTimeout(this.messageTimeout);
                    this.messageTimeout = setTimeout(() => {
                        this.message = { type: '', text: '' };
                    }, 3000);
                },
                normalizeNullable(value) {
                    const normalized = (value ?? '').trim();
                    return normalized === '' ? null : normalized;
                },
                getErrorMessage(payload, fallbackMessage) {
                    if (payload?.message) {
                        return payload.message;
                    }

                    const errors = payload?.errors ?? {};
                    const firstError = Object.values(errors).flat()[0];

                    return firstError || fallbackMessage;
                },
                async patchJson(url, body, fallbackMessage) {
                    const response = await fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify(body),
                    });

                    const payload = await response.json();

                    if (!response.ok) {
                        throw new Error(this.getErrorMessage(payload, fallbackMessage));
                    }

                    return payload;
                },
                openSocial(key, label) {
                    if (this.socials[key]) {
                        window.open(this.socials[key], '_blank', 'noopener,noreferrer');
                        return;
                    }

                    this.showMessage('info', `Add your ${label} link in Edit Personal Information first.`);
                },
                openProfileInfoModal() {
                    this.draftProfile = this.clone(this.profile);
                    this.draftSocials = this.clone(this.socials);
                    this.isProfileInfoModal = true;
                },
                openProfileAddressModal() {
                    this.draftAddress = this.clone(this.address);
                    this.isProfileAddressModal = true;
                },
                async saveProfileInfo() {
                    if (this.isSavingProfileInfo) {
                        return;
                    }

                    this.isSavingProfileInfo = true;

                    try {
                        const payload = await this.patchJson(this.routes.infoUpdate, {
                            full_name: this.normalizeNullable(this.draftProfile.fullName),
                            email: this.normalizeNullable(this.draftProfile.email),
                            phone: this.normalizeNullable(this.draftProfile.phone),
                            role: this.normalizeNullable(this.draftProfile.role),
                            location: this.normalizeNullable(this.draftProfile.location),
                            bio: this.normalizeNullable(this.draftProfile.bio),
                            facebook_url: this.normalizeNullable(this.draftSocials.facebook),
                            x_url: this.normalizeNullable(this.draftSocials.x),
                            linkedin_url: this.normalizeNullable(this.draftSocials.linkedin),
                            instagram_url: this.normalizeNullable(this.draftSocials.instagram),
                        }, 'Unable to update personal information.');

                        this.profile = this.clone(payload.profile);
                        this.socials = this.clone(payload.socials);
                        this.isProfileInfoModal = false;
                        this.showMessage('success', payload.message || 'Personal information updated successfully.');
                    } catch (error) {
                        this.showMessage('error', error.message || 'Unable to update personal information.');
                    } finally {
                        this.isSavingProfileInfo = false;
                    }
                },
                async saveProfileAddress() {
                    if (this.isSavingAddress) {
                        return;
                    }

                    this.isSavingAddress = true;

                    try {
                        const payload = await this.patchJson(this.routes.addressUpdate, {
                            country: this.normalizeNullable(this.draftAddress.country),
                            city_state: this.normalizeNullable(this.draftAddress.cityState),
                            postal_code: this.normalizeNullable(this.draftAddress.postalCode),
                            tax_id: this.normalizeNullable(this.draftAddress.taxId),
                        }, 'Unable to update address information.');

                        this.address = this.clone(payload.address);
                        this.isProfileAddressModal = false;
                        this.showMessage('success', payload.message || 'Address information updated successfully.');
                    } catch (error) {
                        this.showMessage('error', error.message || 'Unable to update address information.');
                    } finally {
                        this.isSavingAddress = false;
                    }
                }
            }
        }
    </script>
@endpush

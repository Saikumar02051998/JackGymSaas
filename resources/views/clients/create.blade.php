<x-layouts.app
    title="Add Client"
    description="Register a new member and get them started."
    :breadcrumbs="[['label' => 'Clients', 'url' => route('clients.index')], ['label' => 'Add Client']]">

    <form method="POST" action="{{ route('clients.store') }}">
        @csrf

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="Personal Information">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-input label="Full name" name="name" value="{{ old('name') }}" required placeholder="e.g. Rohan Sharma" />
                        <x-input label="Email" type="email" name="email" value="{{ old('email') }}" placeholder="client@example.com" />
                        <x-input label="Phone" name="phone" value="{{ old('phone') }}" placeholder="+91 98XXXXXX00" />
                        <x-input label="Emergency Contact" name="emergency_contact" value="{{ old('emergency_contact') }}" placeholder="Name of contact" />
                        <x-input label="Emergency Phone" name="emergency_phone" value="{{ old('emergency_phone') }}" placeholder="+91 98XXXXXX00" />
                        <x-select label="Gender" name="gender" placeholder="Select gender">
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                        </x-select>
                        <x-input label="Date of birth" type="date" name="dob" value="{{ old('dob') }}" />
                        <x-input label="Joining date" type="date" name="joining_date" value="{{ old('joining_date', now()->toDateString()) }}" />
                        <x-select label="Assigned Coach" name="assigned_trainer_id" placeholder="No coach">
                            @foreach ($coaches as $coach)
                                <option value="{{ $coach->id }}" {{ old('assigned_trainer_id') == $coach->id ? 'selected' : '' }}>{{ $coach->display_name }}</option>
                            @endforeach
                        </x-select>
                        <x-input label="Lead Source" name="lead_source" value="{{ old('lead_source') }}" placeholder="Walk-in, Referral, Instagram..." />
                        <div class="sm:col-span-2">
                            <x-input label="Address" name="address" value="{{ old('address') }}" placeholder="Full address" />
                        </div>
                    </div>
                </x-card>

                <x-card title="Health Profile">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <x-input label="Height (cm)" type="number" step="0.1" name="height" value="{{ old('height') }}" />
                        <x-input label="Weight (kg)" type="number" step="0.1" name="weight" value="{{ old('weight') }}" />
                        <x-input label="Body Fat (%)" type="number" step="0.1" name="body_fat" value="{{ old('body_fat') }}" />
                        <x-input label="Goal Weight (kg)" type="number" step="0.1" name="goal_weight" value="{{ old('goal_weight') }}" />
                        <x-select label="Fitness Goal" name="fitness_goal" placeholder="Select a goal">
                            <option value="weight_loss" {{ old('fitness_goal') === 'weight_loss' ? 'selected' : '' }}>Weight Loss</option>
                            <option value="muscle_gain" {{ old('fitness_goal') === 'muscle_gain' ? 'selected' : '' }}>Muscle Gain</option>
                            <option value="strength" {{ old('fitness_goal') === 'strength' ? 'selected' : '' }}>Strength</option>
                            <option value="endurance" {{ old('fitness_goal') === 'endurance' ? 'selected' : '' }}>Endurance</option>
                            <option value="general_fitness" {{ old('fitness_goal') === 'general_fitness' ? 'selected' : '' }}>General Fitness</option>
                        </x-select>
                        <x-select label="Activity Level" name="activity_level" placeholder="Select level">
                            <option value="sedentary" {{ old('activity_level') === 'sedentary' ? 'selected' : '' }}>Sedentary</option>
                            <option value="light" {{ old('activity_level') === 'light' ? 'selected' : '' }}>Light</option>
                            <option value="moderate" {{ old('activity_level') === 'moderate' ? 'selected' : '' }}>Moderate</option>
                            <option value="active" {{ old('activity_level') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="very_active" {{ old('activity_level') === 'very_active' ? 'selected' : '' }}>Very Active</option>
                        </x-select>
                        <div class="sm:col-span-3">
                            <x-field label="Medical Notes" name="medical_notes">
                                <textarea name="medical_notes" rows="2" class="input">{{ old('medical_notes') }}</textarea>
                            </x-field>
                        </div>
                        <div class="sm:col-span-3">
                            <x-field label="Injuries" name="injuries">
                                <textarea name="injuries" rows="2" class="input">{{ old('injuries') }}</textarea>
                            </x-field>
                        </div>
                        <div class="sm:col-span-3">
                            <x-field label="Limitations" name="limitations">
                                <textarea name="limitations" rows="2" class="input">{{ old('limitations') }}</textarea>
                            </x-field>
                        </div>
                        <div class="sm:col-span-3">
                            <x-field label="Allergies" name="allergies">
                                <textarea name="allergies" rows="2" class="input">{{ old('allergies') }}</textarea>
                            </x-field>
                        </div>
                        <div class="sm:col-span-3">
                            <x-field label="Important Notes" name="important_notes">
                                <textarea name="important_notes" rows="2" class="input">{{ old('important_notes') }}</textarea>
                            </x-field>
                        </div>
                    </div>
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card title="Membership Setup">
                    <div class="space-y-4">
                        <label class="flex items-start gap-3 rounded-xl border border-ink-200 p-4 transition-colors has-[:checked]:border-gold-400 has-[:checked]:bg-gold-400/5 dark:border-ink-700">
                            <input type="checkbox" name="start_trial" value="1" class="mt-0.5 size-4 rounded border-ink-300 text-gold-500 focus:ring-gold-400" {{ old('start_trial') ? 'checked' : '' }}>
                            <span>
                                <span class="block text-sm font-semibold text-ink-900 dark:text-white">Start a free trial</span>
                                <span class="mt-0.5 block text-xs text-ink-400">Give the new client a trial period.</span>
                            </span>
                        </label>

                        <div>
                            <x-input label="Trial days" type="number" min="1" max="30" name="trial_days" value="{{ old('trial_days', 7) }}" />
                        </div>

                        <label class="flex items-start gap-3 rounded-xl border border-ink-200 p-4 transition-colors has-[:checked]:border-gold-400 has-[:checked]:bg-gold-400/5 dark:border-ink-700">
                            <input type="checkbox" name="create_membership" value="1" class="mt-0.5 size-4 rounded border-ink-300 text-gold-500 focus:ring-gold-400" {{ old('create_membership') ? 'checked' : '' }}>
                            <span>
                                <span class="block text-sm font-semibold text-ink-900 dark:text-white">Create membership &amp; collect payment</span>
                                <span class="mt-0.5 block text-xs text-ink-400">Register a plan and record the first payment.</span>
                            </span>
                        </label>

                        <div>
                            <x-select label="Membership plan" name="plan_id" placeholder="Select a plan">
                                @foreach (\App\Models\MembershipPlan::where('gym_id', current_gym()?->id)->where('status', 'active')->get() as $plan)
                                    <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} &middot; {{ gym_setting('currency_symbol', '₹') }}{{ number_format($plan->final_amount, 2) }}
                                    </option>
                                @endforeach
                            </x-select>
                        </div>

                        <div>
                            <x-input label="Amount received" type="number" step="0.01" name="amount" value="{{ old('amount') }}" placeholder="0.00" help="Leave blank to skip payment." />
                        </div>
                    </div>
                </x-card>

                <x-card title="Notes">
                    <x-field label="Internal notes" name="notes">
                        <textarea name="notes" rows="4" class="input">{{ old('notes') }}</textarea>
                    </x-field>
                </x-card>

                <div class="flex gap-3">
                    <x-button type="submit" class="flex-1 py-3">
                        <x-icon name="save" class="size-4" />
                        Save Client
                    </x-button>
                    <a href="{{ route('clients.index') }}" class="btn-outline">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</x-layouts.app>

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div>
        <label for="first_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">First name</label>
        <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $student->first_name ?? '') }}" required
               class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-maroon-500 focus:ring-maroon-500">
        @error('first_name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="middle_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
            Middle name <span class="font-normal text-slate-400">(optional)</span>
        </label>
        <input id="middle_name" type="text" name="middle_name" value="{{ old('middle_name', $student->middle_name ?? '') }}"
               class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-maroon-500 focus:ring-maroon-500">
        @error('middle_name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="surname" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Surname</label>
        <input id="surname" type="text" name="surname" value="{{ old('surname', $student->surname ?? '') }}" required
               class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-maroon-500 focus:ring-maroon-500">
        @error('surname') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="student_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Student ID</label>
        <input id="student_id" type="text" name="student_id" value="{{ old('student_id', $student->student_id ?? '') }}" required
               placeholder="e.g. 2026-00123"
               class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-maroon-500 focus:ring-maroon-500">
        @error('student_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div class="md:col-span-2">
        <label for="parent_phone" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Parent's phone number</label>
        <input id="parent_phone" type="tel" name="parent_phone" value="{{ old('parent_phone', $student->parent_phone ?? '') }}" required
               placeholder="e.g. +639171234567"
               class="mt-1 block w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-maroon-500 focus:ring-maroon-500">
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            An SMS is sent to this number every time the student is recognized at the scanner.
            Use the international format (e.g. +63...) for real delivery.
        </p>
        @error('parent_phone') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>
</div>

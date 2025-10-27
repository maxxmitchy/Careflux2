<footer class="bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <!-- Column 1 -->
            <div class="col-span-2 md:col-span-1">
                <a href="" class="flex items-center gap-2 mb-4">
                    {{-- <img class="h-8 w-auto" src="/logo.jpg" alt="Careflux Logo"> --}}
                    <span class="text-xl font-bold text-teal-600">Careflux</span>
                </a>
                <p class="text-xs text-slate-500">Proactive healthcare that actually checks in on you.</p>
            </div>
            <!-- Column 2 -->
            <div>
                <h3 class="text-sm font-semibold text-slate-900">For Patients</h3>
                <ul class="mt-4 space-y-2">
                    <li><a href="{{ route('public.products') }}" class="text-xs text-slate-600 hover:text-teal-600">Search Medications</a></li>
                    <li><a href="{{ route('register') }}" class="text-xs text-slate-600 hover:text-teal-600">Get a Pharmacist</a></li>
                </ul>
            </div>
            <!-- Column 3 -->
            <div>
                 <h3 class="text-sm font-semibold text-slate-900">For Partners</h3>
                <ul class="mt-4 space-y-2">
                    <li><a href="" class="text-xs text-slate-600 hover:text-teal-600">Become a Partner</a></li>
                    <li><a href="" class="text-xs text-slate-600 hover:text-teal-600">Pharmacy Login</a></li>
                </ul>
            </div>
            <!-- Column 4 -->
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Company</h3>
                <ul class="mt-4 space-y-2">
                    <li><a href="" class="text-xs text-slate-600 hover:text-teal-600">Contact Us</a></li>
                    <li><a href="" class="text-xs text-slate-600 hover:text-teal-600">Privacy Policy</a></li>
                    <li><a href="" class="text-xs text-slate-600 hover:text-teal-600">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <div class="mt-8 pt-8 border-t border-slate-200 text-center">
            <p class="text-xs text-slate-500">&copy; {{ date('Y') }} Careflux Technologies. All rights reserved.</p>
        </div>
    </div>
</footer>

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class FrontendGeneratorController extends Controller
{
    public function index()
    {
        return Inertia::render('FrontendGenerator/Index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'routeName' => 'required|string|regex:/^[a-zA-Z0-9\-\/\{\}_]+$/',
            'componentName' => 'required|string|regex:/^[a-zA-Z0-9]+$/',
            'layout' => 'required|array',
        ]);

        $routeUri = ltrim($request->routeName, '/');
        $componentName = ucfirst($request->componentName);
        $layout = $request->layout;

        // Generate Vue File Content
        $vueContent = $this->buildVueComponent($componentName, $layout);

        // Make Generated folder if not exists
        $dir = resource_path('js/Pages/Generated');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // Save Vue File
        $filePath = $dir . '/' . $componentName . '.vue';
        File::put($filePath, $vueContent);

        // Update web.php
        $webRoutePath = base_path('routes/web.php');
        $webContent = File::get($webRoutePath);

        // Parse route parameters for dynamic routing (e.g., /user/{id})
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $routeUri, $matches);
        $params = $matches[1];
        $closureParams = implode(', ', array_map(function($p) { return '$' . $p; }, $params));
        
        $inertiaProps = [];
        foreach($params as $p) {
            $inertiaProps[] = "'$p' => $$p";
        }
        $inertiaPropsStr = empty($inertiaProps) ? '' : ', [' . implode(', ', $inertiaProps) . ']';

        $routeDeclaration = "\nRoute::get('/{$routeUri}', function ({$closureParams}) {\n    return \Inertia\Inertia::render('Generated/{$componentName}'{$inertiaPropsStr});\n});\n";

        // Check if route already exists simple string matching
        if (!Str::contains($webContent, "'/{$routeUri}'") && !Str::contains($webContent, "'{$routeUri}'")) {
            File::append($webRoutePath, $routeDeclaration);
        }

        return response()->json([
            'success' => true,
            'message' => "Page {$componentName} generated successfully at route /{$routeUri}",
            'route' => "/{$routeUri}"
        ]);
    }

    private function buildVueComponent($componentName, $layout)
    {
        $templateInner = "";
        $imports = "import { ref } from 'vue';\n";
        $scriptSetup = "";
        
        // Define standard Vue props if there are dynamic content keys or route props
        $scriptSetup .= "const props = defineProps({\n";
        $scriptSetup .= "  // Route parameters or injected data will be available here\n";
        $scriptSetup .= "});\n";

        $htmlMap = [
            'navbar' => "    <nav class=\"bg-white shadow py-6 px-10 flex justify-between items-center z-50 sticky top-0\">\n      <div class=\"text-2xl font-extrabold text-blue-600 tracking-tighter\">{\Component\}<span class=\"text-gray-800\">.</span></div>\n      <div class=\"hidden md:flex space-x-8 font-medium\">\n        <a href=\"#\" class=\"text-gray-600 hover:text-blue-600 transition\">Home</a>\n        <a href=\"#\" class=\"text-gray-600 hover:text-blue-600 transition\">Services</a>\n        <a href=\"#\" class=\"text-gray-600 hover:text-blue-600 transition\">About</a>\n      </div>\n      <button class=\"bg-blue-600 text-white px-6 py-2 rounded-full font-bold shadow hover:bg-blue-700 transition\">Sign Up</button>\n    </nav>\n",
            'hero' => "    <div class=\"bg-gradient-to-br from-blue-900 to-indigo-800 text-white py-32 px-6 text-center shadow-inner\">\n      <h1 class=\"text-6xl md:text-7xl font-extrabold mb-6 tracking-tight leading-tight\">Build The Future with <br/><span class=\"text-blue-400\">{\Component\}</span></h1>\n      <p class=\"text-2xl text-blue-100 mb-10 max-w-3xl mx-auto font-light\">Experience unprecedented growth and manage your entire digital presence with our revolutionary platform.</p>\n      <div class=\"flex justify-center gap-4\">\n        <button class=\"px-8 py-4 bg-white text-blue-900 font-bold rounded-lg shadow-xl hover:scale-105 transition transform\">Get Started Free</button>\n        <button class=\"px-8 py-4 border-2 border-blue-300 text-blue-50 font-bold rounded-lg hover:bg-blue-800 transition\">Read Documentation</button>\n      </div>\n    </div>\n",
            'hero_split' => "    <div class=\"flex flex-col lg:flex-row items-center py-20 px-8 max-w-7xl mx-auto gap-12\">\n      <div class=\"w-full lg:w-1/2\">\n        <div class=\"inline-block px-3 py-1 mb-4 text-sm font-semibold text-blue-600 bg-blue-100 rounded-full\">New Release 3.0 🎉</div>\n        <h1 class=\"text-5xl lg:text-6xl font-extrabold text-gray-900 mb-6 leading-tight\">Innovate Faster Than Ever.</h1>\n        <p class=\"text-xl text-gray-600 mb-8 font-light\">Build, scale, and secure your next big project with our robust ecosystem and get to market in half the time.</p>\n        <button class=\"px-8 py-4 bg-blue-600 text-white font-bold rounded-lg shadow-lg hover:bg-blue-700 transition lg:mb-0 mb-4\">Start your journey</button>\n      </div>\n      <div class=\"w-full lg:w-1/2 relative\">\n        <div class=\"absolute inset-0 bg-blue-400 rounded-2xl transform translate-x-4 translate-y-4 opacity-20\"></div>\n        <img src=\"https://placehold.co/800x600/2563eb/ffffff?text=Product+Preview\" alt=\"Preview\" class=\"rounded-2xl shadow-2xl relative z-10 w-full object-cover aspect-video\" />\n      </div>\n    </div>\n",
            'features' => "    <div class=\"py-24 bg-gray-50 px-8 border-y border-gray-200\">\n      <div class=\"max-w-7xl mx-auto\">\n        <div class=\"text-center max-w-3xl mx-auto mb-16\">\n          <h2 class=\"text-sm font-bold text-blue-600 tracking-widest uppercase mb-2\">Why Choose Us</h2>\n          <h3 class=\"text-4xl font-extrabold text-gray-900\">Everything you need to succeed</h3>\n        </div>\n        <div class=\"grid grid-cols-1 md:grid-cols-3 gap-10\">\n          <div class=\"bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition\">\n            <div class=\"w-14 h-14 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center mb-6 text-2xl\">⚡</div>\n            <h4 class=\"text-xl font-bold mb-3\">Lightning Fast</h4>\n            <p class=\"text-gray-600 leading-relaxed\">Optimized infrastructure guarantees milliseconds load times across the globe.</p>\n          </div>\n          <div class=\"bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition\">\n            <div class=\"w-14 h-14 bg-green-100 text-green-600 rounded-xl flex items-center justify-center mb-6 text-2xl\">🔒</div>\n            <h4 class=\"text-xl font-bold mb-3\">Ultra Secure</h4>\n            <p class=\"text-gray-600 leading-relaxed\">Bank-grade encryption and automated threat prevention keep your data safe.</p>\n          </div>\n          <div class=\"bg-white p-8 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition\">\n            <div class=\"w-14 h-14 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center mb-6 text-2xl\">🌐</div>\n            <h4 class=\"text-xl font-bold mb-3\">Global Reach</h4>\n            <p class=\"text-gray-600 leading-relaxed\">Deploy your application to 35+ edge locations with just a single click.</p>\n          </div>\n        </div>\n      </div>\n    </div>\n",
            'stats' => "    <div class=\"bg-blue-600 py-16 px-8\">\n      <div class=\"max-w-7xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-blue-500\">\n        <div class=\"px-4\"><div class=\"text-5xl font-extrabold text-white mb-2\">10k+</div><div class=\"text-blue-200 font-medium uppercase tracking-wider text-sm\">Active Users</div></div>\n        <div class=\"px-4\"><div class=\"text-5xl font-extrabold text-white mb-2\">99.9%</div><div class=\"text-blue-200 font-medium uppercase tracking-wider text-sm\">Server Uptime</div></div>\n        <div class=\"px-4\"><div class=\"text-5xl font-extrabold text-white mb-2\">150</div><div class=\"text-blue-200 font-medium uppercase tracking-wider text-sm\">Supported Countries</div></div>\n        <div class=\"px-4\"><div class=\"text-5xl font-extrabold text-white mb-2\">5M</div><div class=\"text-blue-200 font-medium uppercase tracking-wider text-sm\">Daily Requests</div></div>\n      </div>\n    </div>\n",
            'pricing' => "    <div class=\"py-24 max-w-7xl mx-auto px-8\">\n      <div class=\"text-center max-w-2xl mx-auto mb-16\">\n        <h2 class=\"text-4xl font-extrabold text-gray-900 mb-4\">Simple, transparent pricing</h2>\n        <p class=\"text-xl text-gray-500\">No hidden fees. No surprise charges.</p>\n      </div>\n      <div class=\"grid grid-cols-1 md:grid-cols-3 gap-8 items-center\">\n        <div class=\"border border-gray-200 bg-white p-10 rounded-2xl shadow-sm text-center\">\n          <h3 class=\"text-2xl font-semibold mb-2\">Basic</h3>\n          <p class=\"text-gray-500 mb-6\">For individuals starting out</p>\n          <div class=\"text-5xl font-extrabold mb-6\">$9<span class=\"text-lg text-gray-400 font-medium\">/mo</span></div>\n          <ul class=\"space-y-3 text-gray-600 mb-8 text-left\">\n             <li>✓ 1 User Account</li>\n             <li>✓ 5GB Storage</li>\n             <li>✓ Community Support</li>\n          </ul>\n          <button class=\"w-full py-3 bg-blue-50 text-blue-700 font-bold rounded-lg hover:bg-blue-100 transition\">Start Basic</button>\n        </div>\n        <div class=\"border-2 border-blue-600 bg-white p-10 rounded-2xl shadow-xl text-center transform md:-translate-y-4 relative\">\n          <div class=\"absolute top-0 inset-x-0 transform -translate-y-1/2 flex justify-center\"><span class=\"bg-blue-600 text-white text-xs font-bold uppercase tracking-wider py-1 px-4 rounded-full\">Most Popular</span></div>\n          <h3 class=\"text-2xl font-semibold mb-2\">Pro</h3>\n          <p class=\"text-gray-500 mb-6\">For growing professionals</p>\n          <div class=\"text-5xl font-extrabold mb-6\">$29<span class=\"text-lg text-gray-400 font-medium\">/mo</span></div>\n          <ul class=\"space-y-3 text-gray-600 mb-8 text-left\">\n             <li>✓ 5 User Accounts</li>\n             <li>✓ 50GB Storage</li>\n             <li>✓ Priority Email Support</li>\n             <li>✓ Advanced Analytics</li>\n          </ul>\n          <button class=\"w-full py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow-md\">Start Pro Trial</button>\n        </div>\n        <div class=\"border border-gray-200 bg-white p-10 rounded-2xl shadow-sm text-center\">\n          <h3 class=\"text-2xl font-semibold mb-2\">Enterprise</h3>\n          <p class=\"text-gray-500 mb-6\">For large scale operations</p>\n          <div class=\"text-5xl font-extrabold mb-6\">$99<span class=\"text-lg text-gray-400 font-medium\">/mo</span></div>\n          <ul class=\"space-y-3 text-gray-600 mb-8 text-left\">\n             <li>✓ Unlimited Users</li>\n             <li>✓ 1TB Storage</li>\n             <li>✓ 24/7 Phone Support</li>\n          </ul>\n          <button class=\"w-full py-3 bg-blue-50 text-blue-700 font-bold rounded-lg hover:bg-blue-100 transition\">Contact Sales</button>\n        </div>\n      </div>\n    </div>\n",
            'faq' => "    <div class=\"py-24 bg-white px-8\">\n      <div class=\"max-w-4xl mx-auto\">\n        <h2 class=\"text-4xl font-extrabold text-center mb-12 text-gray-900\">Frequently Asked Questions</h2>\n        <div class=\"space-y-6\">\n          <div class=\"bg-gray-50 p-6 rounded-xl border border-gray-100\">\n            <h4 class=\"text-lg font-bold text-gray-900\">How long does it take to integrate?</h4>\n            <p class=\"text-gray-600 mt-2 leading-relaxed\">Integration takes less than 5 minutes. We provide robust SDKs and comprehensive documentation so your development team can hit the ground running immediately.</p>\n          </div>\n          <div class=\"bg-gray-50 p-6 rounded-xl border border-gray-100\">\n            <h4 class=\"text-lg font-bold text-gray-900\">Can I cancel my subscription anytime?</h4>\n            <p class=\"text-gray-600 mt-2 leading-relaxed\">Yes. We believe in earning your business every month. You can downgrade or cancel your plan at any time directly from your billing portal, no questions asked.</p>\n          </div>\n        </div>\n      </div>\n    </div>\n",
            'testimonials' => "    <div class=\"py-24 bg-blue-50 px-8 text-center\">\n      <div class=\"max-w-7xl mx-auto\">\n        <h2 class=\"text-4xl font-extrabold mb-16 text-gray-900\">Loved by businesses worldwide</h2>\n        <div class=\"grid grid-cols-1 md:grid-cols-2 gap-10\">\n          <div class=\"bg-white p-10 rounded-2xl shadow-sm text-left relative\">\n            <div class=\"text-6xl text-blue-200 absolute top-4 left-6 font-serif\">\"</div>\n            <p class=\"italic text-gray-700 text-lg relative z-10 mb-6\">Since moving to {\Component\}, our development cycles have shortened by 40%. The tooling is incredible and the stability is unmatched.</p>\n            <div class=\"flex items-center\">\n               <img src=\"https://placehold.co/100x100/eeeeee/999999?text=JD\" class=\"w-12 h-12 rounded-full mr-4\" />\n               <div><span class=\"font-bold block text-gray-900\">Jane Doe</span><span class=\"text-sm text-gray-500\">CTO at TechFlow</span></div>\n            </div>\n          </div>\n          <div class=\"bg-white p-10 rounded-2xl shadow-sm text-left relative\">\n            <div class=\"text-6xl text-blue-200 absolute top-4 left-6 font-serif\">\"</div>\n            <p class=\"italic text-gray-700 text-lg relative z-10 mb-6\">Customer support is absolutely phenomenal. Whenever we hit a roadblock, the team jumps in quickly with the right solutions.</p>\n            <div class=\"flex items-center\">\n               <img src=\"https://placehold.co/100x100/eeeeee/999999?text=MS\" class=\"w-12 h-12 rounded-full mr-4\" />\n               <div><span class=\"font-bold block text-gray-900\">Michael Smith</span><span class=\"text-sm text-gray-500\">Lead Engineer at StartupX</span></div>\n            </div>\n          </div>\n        </div>\n      </div>\n    </div>\n",
            'logo_cloud' => "    <div class=\"py-12 bg-white border-y border-gray-100\">\n      <div class=\"max-w-7xl mx-auto px-8 text-center\">\n        <p class=\"text-sm text-gray-400 font-bold uppercase tracking-widest mb-8\">Trusted by top innovative companies</p>\n        <div class=\"flex justify-center space-x-12 md:space-x-20 opacity-40 grayscale flex-wrap\">\n          <span class=\"text-2xl font-extrabold tracking-tight\">PiedPiper</span>\n          <span class=\"text-2xl font-extrabold tracking-tight\">Hooli</span>\n          <span class=\"text-2xl font-extrabold tracking-tight\">MassiveDynamic</span>\n          <span class=\"text-2xl font-extrabold tracking-tight\">Initech</span>\n        </div>\n      </div>\n    </div>\n",
            'newsletter' => "    <div class=\"py-24 bg-gray-900 px-8 text-white\">\n      <div class=\"max-w-4xl mx-auto text-center\">\n        <h2 class=\"text-4xl font-extrabold mb-4\">Join our newsletter</h2>\n        <p class=\"text-xl text-gray-400 mb-10 font-light\">Get the latest product updates, articles, and resources sent directly to your inbox every month.</p>\n        <form class=\"flex max-w-lg mx-auto shadow-xl rounded-lg overflow-hidden\" @submit.prevent=\"\">\n          <input type=\"email\" placeholder=\"Enter your email address\" class=\"flex-1 px-6 py-4 text-gray-900 focus:outline-none\">\n          <button class=\"bg-blue-600 hover:bg-blue-500 px-8 py-4 font-bold transition\">Subscribe</button>\n        </form>\n      </div>\n    </div>\n",
            'cta' => "    <div class=\"py-24 px-8 bg-blue-600\">\n      <div class=\"max-w-5xl mx-auto text-center text-white bg-blue-700/50 p-12 rounded-3xl backdrop-blur-sm border border-blue-500/30 shadow-2xl\">\n        <h2 class=\"text-4xl md:text-5xl font-extrabold mb-6\">Ready to accelerate your workflow?</h2>\n        <p class=\"text-xl mb-10 text-blue-100 font-light max-w-2xl mx-auto\">Join thousands of developers building fast, beautiful applications using our premium toolkit.</p>\n        <div class=\"flex justify-center gap-4\">\n          <button class=\"px-8 py-4 bg-white text-blue-600 font-bold rounded-lg shadow-lg hover:scale-105 transition transform\">Start Free 14-day Trial</button>\n        </div>\n      </div>\n    </div>\n",
            'contact' => "    <div class=\"py-24 max-w-2xl mx-auto px-8\">\n      <div class=\"bg-white p-10 rounded-2xl shadow-xl border border-gray-100\">\n        <h2 class=\"text-3xl font-extrabold mb-2 text-gray-900\">Get in touch</h2>\n        <p class=\"text-gray-500 mb-8\">We'd love to hear from you. Fill out the form below.</p>\n        <form @submit.prevent=\"\">\n          <div class=\"grid grid-cols-2 gap-6 mb-6\">\n            <div><label class=\"block text-sm font-semibold text-gray-700 mb-2\">First Name</label><input type=\"text\" class=\"w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition\"></div>\n            <div><label class=\"block text-sm font-semibold text-gray-700 mb-2\">Last Name</label><input type=\"text\" class=\"w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition\"></div>\n          </div>\n          <div class=\"mb-6\">\n            <label class=\"block text-sm font-semibold text-gray-700 mb-2\">Email Address</label>\n            <input type=\"email\" class=\"w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition\">\n          </div>\n          <div class=\"mb-8\">\n            <label class=\"block text-sm font-semibold text-gray-700 mb-2\">Message</label>\n            <textarea rows=\"5\" class=\"w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition\"></textarea>\n          </div>\n          <button class=\"w-full bg-blue-600 text-white font-bold py-4 rounded-lg shadow hover:bg-blue-700 transition\">Send Message</button>\n        </form>\n      </div>\n    </div>\n",
            'table' => "    <div class=\"p-8 max-w-7xl mx-auto py-16\">\n      <div class=\"flex justify-between items-center mb-6\">\n        <div>\n          <h2 class=\"text-2xl font-bold text-gray-900\">Customer Records</h2>\n          <p class=\"text-gray-500 text-sm mt-1\">A list of all users currently active on the platform.</p>\n        </div>\n        <button class=\"bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm\">Add User</button>\n      </div>\n      <div class=\"bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden\">\n        <table class=\"min-w-full divide-y divide-gray-200 text-sm\">\n          <thead class=\"bg-gray-50\">\n            <tr>\n              <th class=\"px-6 py-4 border-b text-left font-semibold text-gray-600 uppercase tracking-wider text-xs\">Name</th>\n              <th class=\"px-6 py-4 border-b text-left font-semibold text-gray-600 uppercase tracking-wider text-xs\">Title</th>\n              <th class=\"px-6 py-4 border-b text-left font-semibold text-gray-600 uppercase tracking-wider text-xs\">Email</th>\n              <th class=\"px-6 py-4 border-b text-left font-semibold text-gray-600 uppercase tracking-wider text-xs\">Role</th>\n              <th class=\"px-6 py-4 border-b text-right font-semibold text-gray-600 uppercase tracking-wider text-xs\">Action</th>\n            </tr>\n          </thead>\n          <tbody class=\"divide-y divide-gray-100\">\n            <tr class=\"hover:bg-gray-50 transition\">\n              <td class=\"px-6 py-4 font-medium text-gray-900\">Lindsay Walton</td>\n              <td class=\"px-6 py-4 text-gray-500\">Front-end Developer</td>\n              <td class=\"px-6 py-4 text-gray-500\">lindsay.walton@example.com</td>\n              <td class=\"px-6 py-4\"><span class=\"px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800\">Member</span></td>\n              <td class=\"px-6 py-4 text-right font-medium\"><a href=\"#\" class=\"text-blue-600 hover:text-blue-900\">Edit</a></td>\n            </tr>\n            <tr class=\"hover:bg-gray-50 transition\">\n              <td class=\"px-6 py-4 font-medium text-gray-900\">Courtney Henry</td>\n              <td class=\"px-6 py-4 text-gray-500\">Designer</td>\n              <td class=\"px-6 py-4 text-gray-500\">courtney.henry@example.com</td>\n              <td class=\"px-6 py-4\"><span class=\"px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800\">Admin</span></td>\n              <td class=\"px-6 py-4 text-right font-medium\"><a href=\"#\" class=\"text-blue-600 hover:text-blue-900\">Edit</a></td>\n            </tr>\n          </tbody>\n        </table>\n      </div>\n    </div>\n",
            'footer_large' => "    <footer class=\"bg-gray-900 text-white pt-20 pb-10 px-8 mt-auto border-t border-gray-800\">\n      <div class=\"max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-12 mb-16\">\n        <div class=\"lg:col-span-2\">\n          <div class=\"text-3xl font-extrabold mb-6 tracking-tighter\">{\Component\}<span class=\"text-blue-500\">.</span></div>\n          <p class=\"text-gray-400 text-sm leading-relaxed max-w-sm mb-6\">Making the world a better place through constructing elegant hierarchies and beautifully fast software.\n          </p>\n          <div class=\"flex space-x-4\">\n            <div class=\"w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-600 transition cursor-pointer\">X</div>\n            <div class=\"w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-600 transition cursor-pointer\">in</div>\n            <div class=\"w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-blue-600 transition cursor-pointer\">fb</div>\n          </div>\n        </div>\n        <div><h4 class=\"font-bold mb-6 tracking-wider uppercase text-sm\">Solutions</h4><ul class=\"space-y-4 text-gray-400 text-sm\"><li><a href=\"#\" class=\"hover:text-white transition\">Marketing</a></li><li><a href=\"#\" class=\"hover:text-white transition\">Analytics</a></li><li><a href=\"#\" class=\"hover:text-white transition\">Commerce</a></li></ul></div>\n        <div><h4 class=\"font-bold mb-6 tracking-wider uppercase text-sm\">Support</h4><ul class=\"space-y-4 text-gray-400 text-sm\"><li><a href=\"#\" class=\"hover:text-white transition\">Pricing</a></li><li><a href=\"#\" class=\"hover:text-white transition\">Documentation</a></li><li><a href=\"#\" class=\"hover:text-white transition\">Guides</a></li></ul></div>\n        <div><h4 class=\"font-bold mb-6 tracking-wider uppercase text-sm\">Company</h4><ul class=\"space-y-4 text-gray-400 text-sm\"><li><a href=\"#\" class=\"hover:text-white transition\">About</a></li><li><a href=\"#\" class=\"hover:text-white transition\">Blog</a></li><li><a href=\"#\" class=\"hover:text-white transition\">Jobs</a></li></ul></div>\n      </div>\n      <div class=\"border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center\">\n        <p class=\"text-gray-500 text-sm\">&copy; 2026 {\Component\} Inc. All rights reserved.</p>\n        <div class=\"flex space-x-6 mt-4 md:mt-0 text-sm text-gray-500\">\n          <a href=\"#\" class=\"hover:text-white\">Privacy Policy</a>\n          <a href=\"#\" class=\"hover:text-white\">Terms of Service</a>\n        </div>\n      </div>\n    </footer>\n"
        ];

        $templateInner .= $this->buildHtmlFromLayout($layout, $htmlMap, $componentName);

        if (empty($layout)) {
            $templateInner = "    <div class=\"flex items-center justify-center h-full text-gray-500 py-20 text-xl font-bold bg-white rounded shadow-sm border m-6\">Empty Layout Generated. Try dragging some components!</div>\n";
        }

        $code = "<template>\n";
        $code .= "  <div class=\"min-h-screen bg-gray-50 flex flex-col font-sans\">\n";
        $code .= $templateInner;
        $code .= "  </div>\n";
        $code .= "</template>\n\n";
        $code .= "<script setup>\n";
        $code .= $imports;
        $code .= $scriptSetup;
        $code .= "</script>\n";

        return $code;
    }

    private function buildHtmlFromLayout($layout, $htmlMap, $componentName) {
        $html = "";
        if (!$layout || !is_array($layout)) return $html;

        foreach($layout as $item) {
            $type = $item['type'] ?? '';
            
            if ($type === 'row') {
                $cols = $item['cols'] ?? 1;
                $columns = $item['columns'] ?? [];
                
                $html .= "    <!-- Grid Row Section -->\n";
                $html .= "    <div class=\"max-w-screen-2xl mx-auto px-6 py-12 w-full\">\n";
                $html .= "      <div class=\"grid grid-cols-1 md:grid-cols-{$cols} gap-8\">\n";
                
                foreach($columns as $colIndex => $colItems) {
                    $html .= "        <div class=\"flex flex-col space-y-6\">\n";
                    $html .= $this->buildHtmlFromLayout($colItems, $htmlMap, $componentName);
                    $html .= "        </div>\n";
                }
                
                $html .= "      </div>\n";
                $html .= "    </div>\n";
                
            } elseif (isset($htmlMap[$type])) {
                $componentHtml = str_replace('{\Component\}', $componentName, $htmlMap[$type]);
                
                // If the user has provided dynamic content, we inject it here
                if (isset($item['content']) && is_array($item['content'])) {
                    // We can replace basic patterns or just prepend an HTML block if it's generic
                    // For now, if there is a 'customHtml' field, we prepend/append or replace specific tags.
                    // To keep it simple, let's just allow replacing the whole block or specific placeholders if we added them.
                    // Since htmlMap has fixed HTML, we'll try to find tags to inject content.
                    
                    if (!empty($item['content']['heading'])) {
                        $componentHtml = preg_replace('/<h[1-6][^>]*>.*?<\/h[1-6]>/s', '<h1 class="text-5xl md:text-6xl font-extrabold mb-6">' . htmlspecialchars($item['content']['heading']) . '</h1>', $componentHtml, 1);
                    }
                    if (!empty($item['content']['subtext'])) {
                        $componentHtml = preg_replace('/<p[^>]*>.*?<\/p>/s', '<p class="text-xl mb-8 font-light">' . htmlspecialchars($item['content']['subtext']) . '</p>', $componentHtml, 1);
                    }
                    if (!empty($item['content']['buttonText'])) {
                        $componentHtml = preg_replace('/<button[^>]*>.*?<\/button>/s', '<button class="px-8 py-4 bg-blue-600 text-white font-bold rounded-lg shadow-lg hover:bg-blue-700 transition">' . htmlspecialchars($item['content']['buttonText']) . '</button>', $componentHtml, 1);
                    }
                }
                
                $html .= $componentHtml;
            }
        }
        return $html;
    }
}

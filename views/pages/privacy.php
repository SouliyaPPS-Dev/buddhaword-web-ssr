<?php
$lastUpdated = 'March 10, 2025';
$appName = 'Buddhaword';
$companyName = 'Buddha Nature';
$contactEmail = 'souliyappsdev@gmail.com';
?>
<section class="py-8"> 
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg p-6 sm:p-8">
            <header class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Privacy Policy</h1>
                <p class="text-gray-600">Last updated: <?= $lastUpdated ?></p>
            </header>

            <div class="prose prose-lg max-w-none text-gray-700 space-y-8">
                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">1. Introduction</h2>
                    <p>
                        Welcome to <?= $appName ?> ("we," "our," or "us"), a Dharma learning
                        app dedicated to sharing the words and teachings of Buddha. This
                        Privacy Policy explains what information we handle when you use
                        our website and mobile experience (the "Service").
                    </p>
                    <p>
                        <?= $appName ?> provides access to Buddhist teachings and related
                        educational content. We designed the app to respect your
                        privacy: we do not require you to create an account and we do
                        not collect personal data for tracking or advertising.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">2. Summary: No Data Collected</h2>
                    <p>
                        We do not collect, sell, or share personal data. The app does
                        not use analytics, advertising SDKs, or third-party trackers.
                    </p>
                    <p>
                        Preferences you set in the app (for example: theme, font size,
                        favorites, and basic navigation history) are stored locally on
                        your device via <code>localStorage</code> or offline cache.
                        These preferences never leave your device unless you choose to
                        share them.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">3. Limited Technical Data</h2>
                    <p>
                        Our hosting providers may receive standard technical logs (such
                        as IP address and basic request metadata) necessary to deliver
                        the Service securely. We do not use this information to track or
                        profile users.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">4. How We Use Information</h2>
                    <p>
                        Device-local preferences are used solely to provide core
                        features (e.g., theme, font size, favorites). We do not use
                        this information for advertising or tracking.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">5. Information Sharing and Disclosure</h2>
                    <p>
                        We do not sell, rent, or share personal data. We may disclose
                        limited technical information only when necessary to comply with
                        law, ensure security, or operate the Service with trusted
                        infrastructure providers.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">6. Data Security</h2>
                    <p>
                        We implement appropriate technical and organizational security
                        measures to protect data in transit and at rest. However, no
                        method of transmission over the internet or electronic storage
                        is 100% secure.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">7. Data Retention</h2>
                    <p>
                        We do not store personal data on our servers. Preferences saved
                        on your device remain there until you delete the app data or
                        clear your browser storage.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">8. Your Rights and Choices</h2>
                    <p>Depending on your location, you may have the following rights:</p>
                    <ul class="list-disc pl-6 space-y-2">
                        <li>Access, correction, or deletion of personal information</li>
                        <li>Restriction or objection to processing</li>
                        <li>Data portability</li>
                        <li>Withdrawal of consent</li>
                    </ul>
                    <p class="mt-4">To exercise these rights, contact us at <?= $contactEmail ?>.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">9. Sacred Content and Buddhist Teachings</h2>
                    <p>
                        <?= $appName ?> is committed to preserving and sharing authentic Buddhist
                        teachings with respect and accuracy.
                    </p>
                    <p>
                        Your interactions with content (e.g., favorites) are stored only
                        on your device to personalize your experience locally.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">10. Third-Party Services</h2>
                    <p>
                        We host media using trusted providers (e.g., Firebase Storage)
                        to deliver content efficiently. We do not send personal data to
                        these providers. If the Service links to other sites, their
                        privacy practices apply; please review their policies.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">11. Children's Privacy</h2>
                    <p>
                        Our Service is not intended for children under 13. We do not
                        knowingly collect personal information from children. If you
                        believe a child has provided personal information, please
                        contact us immediately.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">12. International Data Transfers</h2>
                    <p>
                        Data may be processed in countries other than your own to
                        deliver the Service. We ensure appropriate safeguards where
                        applicable.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">13. Changes to This Privacy Policy</h2>
                    <p>
                        We may update this Privacy Policy from time to time. We will
                        post updates here and revise the "Last updated" date above.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">14. Contact Information</h2>
                    <p>If you have questions, please contact us:</p>
                    <div class="bg-gray-100 p-4 rounded-lg mt-4">
                        <p><strong><?= $companyName ?></strong></p>
                        <p>Email: <?= $contactEmail ?></p>
                        <p>Address: Laos</p>
                        <p>Phone: +856 2078287509</p>
                    </div>
                </section>
            </div>

            <footer class="mt-12 pt-8 border-t border-gray-200 text-center text-sm text-gray-500">
                <p>&copy; 2025 <?= $companyName ?>. All rights reserved.</p>
            </footer>
        </div>
    </div>
</section>

// --- THIS IS THE MODIFIED, ESM-COMPATIBLE SCRIPT ---

import fs from 'fs';
import mysql from 'mysql2/promise';
import { execSync } from 'child_process';

(async () => {
    console.log('🚀 Starting product sync process...');

    const config = JSON.parse(fs.readFileSync('./sync-config.json', 'utf-8'));
    const { output_file, mysql: dbConfig, vps, remote_import_command } = config;
    const dryRun = process.argv.includes('--dry-run');

    try {
        console.log('[1/5] Connecting to local MySQL database...');
        const connection = await mysql.createConnection(dbConfig);

        console.log('[2/5] Fetching all scraped products with store names...');
        
        // --- THIS IS THE MODIFIED QUERY TO MATCH YOUR NEW MIGRATION ---
        // It selects the correct columns from the 'scraped_products' table.
        const [products] = await connection.execute(
            `SELECT
                s.name AS store_name,
                p.store_id AS local_store_id,
                p.product_name,
                p.product_url,
                p.image_url,
                p.price,
                p.stock_status,
                p.brand,
                p.external_id,
                p.search_keyword,
                p.is_blacklisted,
                p.soundex_name
            FROM scraped_products p
            JOIN stores s ON p.store_id = s.id
            WHERE p.store_id = ?`,
            [4] // Example store_id, change if needed
        );
        // --- END OF MODIFICATION ---

        await connection.end();

        if (products.length === 0) {
            console.warn('⚠️ No products found in the local database for the specified store. Nothing to sync.');
            process.exit(0);
        }
        console.log(`[✓] Found ${products.length} products to sync.`);

        if (dryRun) {
            console.log('\n--- DRY RUN MODE ---');
            console.log(`Sample of 3 products:`, products.slice(0, 3));
            console.log('Aborting before writing files or executing commands.');
            process.exit(0);
        }

        console.log(`[3/5] Writing ${products.length} products to ${output_file}...`);
        fs.writeFileSync(output_file, JSON.stringify(products, null, 2));
        console.log(`[✓] File created successfully.`);

        const remotePath = `${vps.project_path}/storage/app/${output_file}`;
        const scpCmd = `scp ${output_file} ${vps.user}@${vps.host}:${remotePath}`;

        console.log(`[4/5] Uploading ${output_file} to VPS...`);
        execSync(scpCmd, { stdio: 'inherit' });
        console.log(`[✓] Upload complete.`);

        if (remote_import_command) {
            const sshCmd = `ssh ${vps.user}@${vps.host} "cd ${vps.project_path} && ${remote_import_command}"`;
            console.log(`[5/5] Executing remote import command on VPS...`);
            execSync(sshCmd, { stdio: 'inherit' });
            console.log('[✓] Remote import command finished.');
        }

        console.log('\n✅ Sync process completed successfully!');

    } catch (err) {
        console.error('\n❌ An error occurred during the sync process:', err.message || err);
        process.exit(1);
    }
})();
Step-by-Step Release Guide
Step 1: Commit your latest calendar fixes and version updates First, save all the calendar adjustments and version bumps to your repository:

bash
git add .
git commit -m "Fix 2026-2028 calendar sequences and month gaps, bump version to 1.3"
Step 2: Create the new version tag locally Create a new Git tag for version 1.3.0. Packagist uses these tags to determine the versions of your package:

bash
git tag v1.3.0
Step 3: Push the code to GitHub Push your main code updates to your remote repository. (Note: If you run into the same authentication error as before, make sure you are using your GitHub Personal Access Token or SSH key when prompted for a password!)

bash
git push origin main
Step 4: Push the new tag to GitHub This is the most crucial step! Your new tag must be pushed to GitHub so Packagist knows v1.3.0 exists:

bash
git push origin v1.3.0
Step 5: Verify on Packagist (Optional but recommended)

Go to your package page on Packagist.org.
Click the "Update" button if your GitHub webhook hasn't already triggered it automatically.
You should see 1.3.0 listed under your available versions! Once it's there, you can successfully install it in your test project.

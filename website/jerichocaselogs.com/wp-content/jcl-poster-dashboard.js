/**
 * JCL Job Poster Dashboard
 * Ergonomic landing page for job posters after login
 * Version: 1.0
 */

(function() {
  'use strict';

  // Check if Parse is loaded
  if (typeof Parse === 'undefined') {
    console.error('[JCL Dashboard] Parse SDK not loaded');
    document.getElementById('jcl-dashboard-root')?.insertAdjacentHTML('beforeend',
      '<div style="padding: 40px; text-align: center; color: #dc3545;">Unable to load dashboard. Please refresh the page.</div>'
    );
    return;
  }

  // Initialize Parse (using credentials from signup modal)
  const PARSE_APP_ID = '9Zso4z2xN8gTLfauAqShE7gMkYAaDav3HoTGFimF';
  const PARSE_JS_KEY = 'xjXXK53D2IQh00KvpNJUWI2U5jNtMprxE2OegXFI';
  const PARSE_SERVER_URL = 'https://parseapi.back4app.com/';

  Parse.initialize(PARSE_APP_ID, PARSE_JS_KEY);
  Parse.serverURL = PARSE_SERVER_URL;

  // Global dashboard object
  window.JCLDashboard = {
    currentUser: null,
    userProfile: null,
    organization: null,
    costPerPost: 1, // Default cost, will be fetched from AppSettings

    /**
     * Initialize dashboard
     */
    async init() {
      const rootEl = document.getElementById('jcl-dashboard-root');
      if (!rootEl) {
        console.error('[JCL Dashboard] Root element not found');
        return;
      }

      // Check authentication
      this.currentUser = Parse.User.current();

      if (!this.currentUser) {
        this.showLoginPrompt(rootEl);
        return;
      }

      // Show loading state
      rootEl.innerHTML = '<div class="jcl-dash-loading"><div class="jcl-dash-spinner"></div><p>Loading your dashboard...</p></div>';

      try {
        // Fetch user profile data
        await this.fetchUserData();

        // Render dashboard
        this.renderDashboard(rootEl);

      } catch (error) {
        console.error('[JCL Dashboard] Init error:', error);
        rootEl.innerHTML = `
          <div class="jcl-dash-error">
            <p>Unable to load dashboard</p>
            <p class="jcl-dash-error-detail">${error.message}</p>
            <button onclick="location.reload()" class="jcl-dash-btn jcl-dash-btn-primary">Retry</button>
          </div>
        `;
      }
    },

    /**
     * Show login prompt if not authenticated
     */
    showLoginPrompt(rootEl) {
      rootEl.innerHTML = `
        <div class="jcl-dash-login-prompt">
          <div class="jcl-dash-login-icon">🔒</div>
          <h2>Access Required</h2>
          <p>Please log in to access your employer dashboard</p>
          <button onclick="JCLDashboard.triggerLogin()" class="jcl-dash-btn jcl-dash-btn-primary">Log In</button>
        </div>
      `;
    },

    /**
     * Trigger existing login modal
     */
    triggerLogin() {
      // Try to trigger existing login modal
      if (typeof openLoginModal === 'function') {
        openLoginModal();
      } else {
        // Fallback: redirect to login page if it exists
        const loginUrl = document.querySelector('a[href*="login"]')?.href;
        if (loginUrl) {
          window.location.href = loginUrl;
        } else {
          alert('Please navigate to the login page');
        }
      }
    },

    /**
     * Fetch user data from Parse
     */
    async fetchUserData() {
      this.currentUser = Parse.User.current();

      // Try to fetch PosterProfile
      try {
        const PosterProfile = Parse.Object.extend('PosterProfile');
        const query = new Parse.Query(PosterProfile);
        query.equalTo('user', this.currentUser);
        query.include('organization');

        this.userProfile = await query.first();

        if (this.userProfile) {
          this.organization = this.userProfile.get('organization');
          // Refresh organization to get latest data from server
          if (this.organization) {
            await this.organization.fetch();
          }
        }
      } catch (error) {
        console.warn('[JCL Dashboard] Could not fetch PosterProfile:', error);
        // Continue with basic user data
      }

      // Refresh current user to get latest data
      await this.currentUser.fetch();

      // Fetch app settings (cost per post, etc.)
      await this.fetchAppSettings();
    },

    /**
     * Fetch app settings from AppSettings table
     */
    async fetchAppSettings() {
      try {
        const AppSettings = Parse.Object.extend('AppSettings');
        const query = new Parse.Query(AppSettings);

        // Get the first (and should be only) settings record
        const settings = await query.first();

        if (settings) {
          // Get cost per post, default to 1 if not set
          this.costPerPost = settings.get('costPerPost') || 1;
        } else {
          // No settings found, use default
          this.costPerPost = 1;
        }
      } catch (error) {
        console.warn('[JCL Dashboard] Could not fetch app settings:', error);
        // Use default cost
        this.costPerPost = 1;
      }
    },

    /**
     * Get display name (try multiple fields)
     */
    getDisplayName() {
      if (this.userProfile) {
        const firstName = this.userProfile.get('firstName');
        const lastName = this.userProfile.get('lastName');
        if (firstName && lastName) {
          return `${firstName} ${lastName}`;
        }
        if (firstName) return firstName;
      }

      // Fallback to user fields
      const facName = this.currentUser.get('facContactName');
      if (facName) return facName;

      const username = this.currentUser.get('username');
      if (username) return username;

      return 'User';
    },

    /**
     * Get organization name
     */
    getOrgName() {
      if (this.organization) {
        return this.organization.get('organizationName') || this.organization.get('facName');
      }

      // Fallback to user's facName field
      const facName = this.currentUser.get('facName');
      if (facName) return facName;

      return 'Your Organization';
    },

    /**
     * Get verification status
     */
    getVerificationStatus() {
      // Check organization verification
      if (this.organization) {
        const status = this.organization.get('verificationStatus');
        if (status === 'verified') return 'verified';
        if (status === 'pending') return 'pending';
      }

      // Check user-level verification
      const verifiedEmail = this.currentUser.get('verifiedEmail');
      const orgVerified = this.currentUser.get('orgVerified');

      if (verifiedEmail === true || orgVerified === true) {
        return 'verified';
      }

      return 'pending';
    },

    /**
     * Render main dashboard
     */
    async renderDashboard(rootEl) {
      const displayName = this.getDisplayName();
      const orgName = this.getOrgName();
      const verificationStatus = this.getVerificationStatus();
      const isVerified = verificationStatus === 'verified';

      const email = this.userProfile?.get('user')?.get('email') || this.currentUser.get('email') || '';
      const phone = this.userProfile?.get('phone') || this.currentUser.get('facContactPhone') || this.currentUser.get('phone') || '';
      const title = this.userProfile?.get('title') || '';

      // Check for notifications
      const hasNotifications = !isVerified;

      // Inject Dashboard title and icons into WordPress header
      this.injectHeaderElements(hasNotifications);

      // Get posting limit and count
      const postingLimit = this.organization?.get('postingLimit') || (isVerified ? 10 : 1);
      const currentPostsCount = await this.getJobPostsCount();
      const creditsRemaining = Math.max(0, postingLimit - currentPostsCount);

      rootEl.innerHTML = `
        <div class="jcl-dash-container">

          <!-- Welcome Section with Credits on Same Line -->
          <div class="jcl-dash-welcome-section">
            <div class="jcl-dash-welcome-left">
              <h2>Welcome, ${this.escapeHtml(displayName)}</h2>
              <p class="jcl-dash-org-name">${this.escapeHtml(orgName)}</p>
            </div>

            <!-- Credits Display (parallel to welcome) -->
            <div class="jcl-dash-credits-inline">
              <div class="jcl-dash-credits-item">
                <span class="jcl-dash-credits-label">Available Credits:</span>
                <span class="jcl-dash-credits-value ${creditsRemaining === 0 ? 'jcl-credits-depleted' : ''}">${creditsRemaining} / ${postingLimit}</span>
              </div>
              <div class="jcl-dash-credits-item">
                <span class="jcl-dash-credits-label">Cost per Job Post:</span>
                <span class="jcl-dash-credits-value">${this.costPerPost} ${this.costPerPost === 1 ? 'Credit' : 'Credits'}</span>
              </div>
              <button class="jcl-dash-btn jcl-dash-btn-secondary jcl-dash-btn-small" onclick="JCLDashboard.showPurchaseCreditsModal()" style="margin-top: 8px;">
                💳 Purchase More Credits
              </button>
              ${!isVerified ? '<div class="jcl-dash-credits-warning-text">⚠️ Account pending verification</div>' : ''}
            </div>
          </div>

          <!-- Primary Action Strip -->
          <div class="jcl-dash-actions-primary">
            <button class="jcl-dash-btn jcl-dash-btn-primary jcl-dash-btn-large" onclick="JCLDashboard.postJob()">
              <span class="jcl-dash-btn-icon">✚</span>
              Post a Job
            </button>
          </div>

          <!-- Posted Jobs Section -->
          <div class="jcl-dash-jobs-section">
            <h2 class="jcl-dash-section-title">Your Posted Jobs</h2>
            <div id="jcl-dash-jobs-list" class="jcl-dash-jobs-list">
              <div class="jcl-dash-loading">
                <div class="jcl-dash-spinner"></div>
                <p>Loading jobs...</p>
              </div>
            </div>
          </div>

        </div>
      `;

      // Load and render jobs
      await this.loadAndRenderJobs();

      // Initialize meteor animation (same as home page)
      try {
        this.initializeMeteorAnimation();
      } catch (error) {
        console.error('[JCL Dashboard] Meteor animation error:', error);
      }
    },

    /**
     * Inject Dashboard title, icons, and banner into WordPress site header
     */
    injectHeaderElements(hasNotifications) {
      // Try to find the WordPress header navigation
      const wpHeader = document.querySelector('header nav, header .nav, header .menu, #site-navigation, .site-header');

      if (!wpHeader) {
        console.warn('[JCL Dashboard] Could not find WordPress header to inject elements');
        return;
      }

      // Remove any existing dashboard elements
      const existingLeft = document.getElementById('jcl-dashboard-header-left');
      const existingRight = document.getElementById('jcl-dashboard-header-right');
      const existingCenter = document.querySelector('.jcl-header-center-banner');
      if (existingLeft) existingLeft.remove();
      if (existingRight) existingRight.remove();
      if (existingCenter) existingCenter.remove();

      // Set up header to use flexbox for proper layout
      wpHeader.style.display = 'flex';
      wpHeader.style.alignItems = 'center';
      wpHeader.style.justifyContent = 'space-between';
      wpHeader.style.position = 'relative';

      // Create LEFT section (Dashboard title)
      const leftContainer = document.createElement('div');
      leftContainer.id = 'jcl-dashboard-header-left';
      leftContainer.style.cssText = 'display: flex; align-items: center; order: 1;';

      const dashboardTitle = document.createElement('span');
      dashboardTitle.className = 'jcl-header-dashboard-title';
      dashboardTitle.textContent = 'Dashboard';
      dashboardTitle.style.cssText = 'font-size: 18px; font-weight: 600; color: #FFFFFF;';
      leftContainer.appendChild(dashboardTitle);

      // Create CENTER section (JCL Banner)
      const centerContainer = document.createElement('div');
      centerContainer.className = 'jcl-header-center-banner';
      centerContainer.style.cssText = 'position: absolute; left: 50%; transform: translateX(-50%); order: 2; z-index: 1;';
      centerContainer.innerHTML = `
        <img src="https://www.jerichocaselogs.com/wp-content/uploads/2025/12/jclBanner_3d.png"
             alt="Jericho Case Logs"
             style="width: 143px; height: auto; display: block;"
        />
      `;

      // Create RIGHT section (Icon buttons)
      const rightContainer = document.createElement('div');
      rightContainer.id = 'jcl-dashboard-header-right';
      rightContainer.style.cssText = 'display: flex; gap: 12px; align-items: center; margin-left: auto; order: 3;';

      const iconsHTML = `
        <button class="jcl-dash-icon-btn" onclick="JCLDashboard.openManageAccount()" title="Manage Account">
          <span class="jcl-icon">⚙️</span>
        </button>
        <button class="jcl-dash-icon-btn" onclick="JCLDashboard.viewProfile()" title="Your Profile">
          <span class="jcl-icon">👤</span>
        </button>
        <button class="jcl-dash-icon-btn ${hasNotifications ? 'jcl-has-notification' : ''}" onclick="JCLDashboard.viewNotifications()" title="Notifications">
          <span class="jcl-icon">🔔</span>
          ${hasNotifications ? '<span class="jcl-notification-dot"></span>' : ''}
        </button>
      `;
      rightContainer.insertAdjacentHTML('beforeend', iconsHTML);

      // Append all sections to header
      wpHeader.insertBefore(leftContainer, wpHeader.firstChild);
      wpHeader.appendChild(centerContainer);
      wpHeader.appendChild(rightContainer);
    },

    /**
     * Post a job - open modal (check credits first)
     */
    async postJob() {
      // Check available credits (use same logic as renderDashboard)
      const verificationStatus = this.getVerificationStatus();
      const isVerified = verificationStatus === 'verified';
      const postingLimit = this.organization?.get('postingLimit') || (isVerified ? 10 : 1);
      const currentPostsCount = await this.getJobPostsCount();
      const creditsRemaining = Math.max(0, postingLimit - currentPostsCount);

      // If no credits available, trigger payment flow
      if (creditsRemaining === 0) {
        this.showPurchaseCreditsModal();
        return;
      }

      // Otherwise, open job posting modal
      this.renderPostJobModal();
    },

    /**
     * Complete verification flow
     */
    completeVerification() {
      alert('Your account verification is automatic.\n\nIf you see a "Pending" status, please contact support for assistance.');
    },

    /**
     * Open manage account modal
     */
    openManageAccount() {
      this.renderManageAccountModal();
    },

    /**
     * Render manage account modal
     */
    renderManageAccountModal() {
      // Get current values
      const firstName = this.userProfile?.get('firstName') || '';
      const lastName = this.userProfile?.get('lastName') || '';
      const phone = this.userProfile?.get('phone') || this.currentUser.get('facContactPhone') || this.currentUser.get('phone') || '';
      const title = this.userProfile?.get('title') || '';

      // Create modal HTML
      const modalHTML = `
        <div id="jcl-manage-account-modal" class="jcl-dash-modal">
          <div class="jcl-dash-modal-overlay" onclick="JCLDashboard.closeManageAccount()"></div>
          <div class="jcl-dash-modal-content">
            <button class="jcl-dash-modal-close" onclick="JCLDashboard.closeManageAccount()" aria-label="Close">&times;</button>

            <h2 class="jcl-dash-modal-title">Manage Account</h2>
            <p class="jcl-dash-modal-subtitle">Update your profile information</p>

            <form id="jcl-manage-account-form">

              <div class="jcl-dash-form-group">
                <label for="jcl-edit-firstname" class="jcl-dash-form-label">First Name</label>
                <input
                  type="text"
                  id="jcl-edit-firstname"
                  class="jcl-dash-form-input"
                  value="${this.escapeHtml(firstName)}"
                  required
                >
              </div>

              <div class="jcl-dash-form-group">
                <label for="jcl-edit-lastname" class="jcl-dash-form-label">Last Name</label>
                <input
                  type="text"
                  id="jcl-edit-lastname"
                  class="jcl-dash-form-input"
                  value="${this.escapeHtml(lastName)}"
                  required
                >
              </div>

              <div class="jcl-dash-form-group">
                <label for="jcl-edit-phone" class="jcl-dash-form-label">Phone</label>
                <input
                  type="tel"
                  id="jcl-edit-phone"
                  class="jcl-dash-form-input"
                  value="${this.escapeHtml(phone)}"
                  placeholder="(555) 123-4567"
                  pattern="[0-9\\(\\)\\s\\-\\+]+"
                >
                <small class="jcl-dash-form-hint">Format: (555) 123-4567</small>
              </div>

              <div class="jcl-dash-form-group">
                <label for="jcl-edit-title" class="jcl-dash-form-label">Job Title</label>
                <input
                  type="text"
                  id="jcl-edit-title"
                  class="jcl-dash-form-input"
                  value="${this.escapeHtml(title)}"
                  placeholder="e.g., HR Manager"
                >
              </div>

              <div id="jcl-manage-account-message" class="jcl-dash-message" style="display: none;"></div>

              <div class="jcl-dash-modal-actions">
                <button type="button" class="jcl-dash-btn jcl-dash-btn-secondary" onclick="JCLDashboard.closeManageAccount()">
                  Cancel
                </button>
                <button type="submit" class="jcl-dash-btn jcl-dash-btn-primary">
                  Save Changes
                </button>
              </div>

            </form>

          </div>
        </div>
      `;

      // Insert modal into page
      document.body.insertAdjacentHTML('beforeend', modalHTML);

      // Add form submit handler
      document.getElementById('jcl-manage-account-form').addEventListener('submit', (e) => {
        e.preventDefault();
        this.saveAccountChanges();
      });

      // Add ESC key handler
      this.modalEscHandler = (e) => {
        if (e.key === 'Escape') {
          this.closeManageAccount();
        }
      };
      document.addEventListener('keydown', this.modalEscHandler);
    },

    /**
     * Save account changes
     */
    async saveAccountChanges() {
      const messageEl = document.getElementById('jcl-manage-account-message');
      const submitBtn = document.querySelector('#jcl-manage-account-form button[type="submit"]');

      // Get values
      const firstName = document.getElementById('jcl-edit-firstname').value.trim();
      const lastName = document.getElementById('jcl-edit-lastname').value.trim();
      const phone = document.getElementById('jcl-edit-phone').value.trim();
      const title = document.getElementById('jcl-edit-title').value.trim();

      // Validate
      if (!firstName || !lastName) {
        this.showMessage(messageEl, 'First and last name are required', 'error');
        return;
      }

      // Disable button
      submitBtn.disabled = true;
      submitBtn.textContent = 'Saving...';

      try {
        // Update PosterProfile if exists
        if (this.userProfile) {
          this.userProfile.set('firstName', firstName);
          this.userProfile.set('lastName', lastName);
          if (phone) this.userProfile.set('phone', phone);
          if (title) this.userProfile.set('title', title);
          await this.userProfile.save();
        }

        // Also update User fields as fallback
        this.currentUser.set('facContactName', `${firstName} ${lastName}`);
        if (phone) this.currentUser.set('facContactPhone', phone);
        await this.currentUser.save();

        this.showMessage(messageEl, 'Profile updated successfully!', 'success');

        // Refresh dashboard after short delay
        setTimeout(() => {
          this.closeManageAccount();
          this.init(); // Reload dashboard
        }, 1500);

      } catch (error) {
        console.error('[JCL Dashboard] Save error:', error);
        this.showMessage(messageEl, `Error: ${error.message}`, 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Save Changes';
      }
    },

    /**
     * Close manage account modal
     */
    closeManageAccount() {
      const modal = document.getElementById('jcl-manage-account-modal');
      if (modal) {
        modal.remove();
      }
      if (this.modalEscHandler) {
        document.removeEventListener('keydown', this.modalEscHandler);
      }
    },

    /**
     * Show message in modal
     */
    showMessage(el, text, type) {
      el.textContent = text;
      el.className = `jcl-dash-message jcl-dash-message-${type}`;
      el.style.display = 'block';
    },

    /**
     * Render post job modal
     */
    renderPostJobModal() {
      const usStates = ['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY'];

      // Get organization info
      const orgType = this.organization?.get('organizationType') || '';
      const isFacility = orgType === 'Facility' || orgType === 'Hospital' || orgType === 'Clinic';

      // Pre-fill values for facilities
      const facilityName = isFacility ? (this.organization?.get('facilityName') || this.organization?.get('organizationName') || '') : '';
      const ccnNpi = isFacility ? (this.organization?.get('ccn') || this.organization?.get('npi') || '') : '';

      // Get saved city/state from organization
      const savedCity = this.organization?.get('defaultCity') || '';
      const savedState = this.organization?.get('defaultState') || '';

      const modalHTML = `
        <div id="jcl-post-job-modal" class="jcl-dash-modal">
          <div class="jcl-dash-modal-overlay" onclick="JCLDashboard.closePostJob()"></div>
          <div class="jcl-dash-modal-content" style="max-width: 700px;">
            <button class="jcl-dash-modal-close" onclick="JCLDashboard.closePostJob()" aria-label="Close">&times;</button>

            <h2 class="jcl-dash-modal-title">Post a New Job</h2>
            <p class="jcl-dash-modal-subtitle">Fill out the details for your job posting</p>

            <form id="jcl-post-job-form">

              <!-- Professional Title -->
              <div class="jcl-dash-form-group">
                <label for="jcl-job-professional-title" class="jcl-dash-form-label">Professional Title *</label>
                <select id="jcl-job-professional-title" class="jcl-dash-form-input" required>
                  <option value="">Select professional title</option>
                  <option value="CRNA">CRNA</option>
                  <option value="CAA">CAA</option>
                  <option value="Anesthesia">Anesthesia</option>
                  <option value="Registered Nurse(RN)">Registered Nurse (RN)</option>
                  <option value="CNA">CNA</option>
                  <option value="LPN/LVN">LPN/LVN</option>
                  <option value="NP">NP</option>
                  <option value="CNS">CNS</option>
                </select>
              </div>

              <!-- Facility Name -->
              <div class="jcl-dash-form-group">
                <label for="jcl-job-facility-name" class="jcl-dash-form-label">
                  Facility Name *
                  ${facilityName ? '<button type="button" class="jcl-edit-field-btn" onclick="JCLDashboard.enableField(\'jcl-job-facility-name\')" title="Edit">✎ Edit</button>' : ''}
                </label>
                <input
                  type="text"
                  id="jcl-job-facility-name"
                  class="jcl-dash-form-input"
                  placeholder="e.g., Memorial Hospital"
                  value="${this.escapeHtml(facilityName)}"
                  ${facilityName ? 'readonly style="background-color: #f5f5f5; color: #666;"' : ''}
                  required
                >
              </div>

              <!-- CCN Number -->
              <div class="jcl-dash-form-group">
                <label for="jcl-job-ccn-npi" class="jcl-dash-form-label">
                  CCN Number *
                  ${ccnNpi ? '<button type="button" class="jcl-edit-field-btn" onclick="JCLDashboard.enableField(\'jcl-job-ccn-npi\')" title="Edit">✎ Edit</button>' : ''}
                </label>
                <input
                  type="text"
                  id="jcl-job-ccn-npi"
                  class="jcl-dash-form-input"
                  placeholder="Enter CCN number"
                  value="${this.escapeHtml(ccnNpi)}"
                  ${ccnNpi ? 'readonly style="background-color: #f5f5f5; color: #666;"' : ''}
                  required
                >
                <small class="jcl-dash-form-hint">Facility verification number</small>
              </div>

              <!-- City -->
              <div class="jcl-dash-form-group">
                <label for="jcl-job-city" class="jcl-dash-form-label">
                  City *
                  ${savedCity ? '<button type="button" class="jcl-edit-field-btn" onclick="JCLDashboard.enableField(\'jcl-job-city\')" title="Edit">✎ Edit</button>' : ''}
                </label>
                <input
                  type="text"
                  id="jcl-job-city"
                  class="jcl-dash-form-input"
                  placeholder="e.g., Chicago"
                  value="${this.escapeHtml(savedCity)}"
                  ${savedCity ? 'readonly style="background-color: #f5f5f5; color: #666;"' : ''}
                  required
                >
              </div>

              <!-- State -->
              <div class="jcl-dash-form-group">
                <label for="jcl-job-state" class="jcl-dash-form-label">
                  State *
                  ${savedState ? '<button type="button" class="jcl-edit-field-btn" onclick="JCLDashboard.enableField(\'jcl-job-state\')" title="Edit">✎ Edit</button>' : ''}
                </label>
                <select id="jcl-job-state" class="jcl-dash-form-input" ${savedState ? 'disabled style="background-color: #f5f5f5; color: #666;"' : ''} required>
                  <option value="">Select state</option>
                  ${usStates.map(state => `<option value="${state}" ${state === savedState ? 'selected' : ''}>${state}</option>`).join('')}
                </select>
              </div>

              <!-- Facility/City Visibility -->
              <div class="jcl-dash-form-group">
                <label class="jcl-dash-checkbox-container">
                  <input type="checkbox" id="jcl-job-hide-facility" checked>
                  <span class="jcl-dash-checkbox-label">Hide facility name & city in job posting</span>
                </label>
                <small class="jcl-dash-form-hint">When checked, only the state will be visible to applicants. This prevents direct applications and protects your facility information.</small>
              </div>

              <!-- Job Type -->
              <div class="jcl-dash-form-group">
                <label for="jcl-job-type" class="jcl-dash-form-label">Job Type *</label>
                <select id="jcl-job-type" class="jcl-dash-form-input" required>
                  <option value="">Select job type</option>
                  <option value="Locum">Locum</option>
                  <option value="Permanent">Permanent</option>
                  <option value="Both">Both</option>
                </select>
              </div>

              <!-- Job Duration -->
              <div class="jcl-dash-form-group">
                <label for="jcl-job-duration" class="jcl-dash-form-label">Job Duration *</label>
                <select id="jcl-job-duration" class="jcl-dash-form-input" required>
                  <option value="">Select duration</option>
                  <option value="3 months">3 months</option>
                  <option value="6 months">6 months</option>
                  <option value="9 months">9 months</option>
                  <option value="12 months">12 months</option>
                  <option value="Flexible">Flexible</option>
                </select>
              </div>

              <!-- Job Status -->
              <div class="jcl-dash-form-group">
                <label for="jcl-job-status" class="jcl-dash-form-label">Job Status *</label>
                <select id="jcl-job-status" class="jcl-dash-form-input" required>
                  <option value="">Select status</option>
                  <option value="Active">Active</option>
                  <option value="On Hold">On Hold</option>
                </select>
              </div>

              <!-- Emergent Status -->
              <div class="jcl-dash-form-group">
                <label for="jcl-job-emergent" class="jcl-dash-form-label">Emergent Status *</label>
                <select id="jcl-job-emergent" class="jcl-dash-form-input" required>
                  <option value="">Select emergent status</option>
                  <option value="Yes">Yes</option>
                  <option value="No">No</option>
                </select>
              </div>

              <!-- Requisition Number -->
              <div class="jcl-dash-form-group">
                <label for="jcl-job-requisition" class="jcl-dash-form-label">Requisition Number</label>
                <input
                  type="text"
                  id="jcl-job-requisition"
                  class="jcl-dash-form-input"
                  placeholder="e.g., REQ-2025-001"
                >
              </div>

              <!-- Specialty -->
              <div class="jcl-dash-form-group">
                <label for="jcl-job-specialty" class="jcl-dash-form-label">Specialty</label>
                <input
                  type="text"
                  id="jcl-job-specialty"
                  class="jcl-dash-form-input"
                  placeholder="e.g., ICU, Emergency, Pediatrics"
                >
              </div>

              <!-- Job Description -->
              <div class="jcl-dash-form-group">
                <label for="jcl-job-description" class="jcl-dash-form-label">Job Description *</label>
                <textarea
                  id="jcl-job-description"
                  class="jcl-dash-form-input"
                  rows="4"
                  placeholder="Describe the position, responsibilities, and requirements..."
                  required
                  style="resize: vertical; font-family: inherit;"
                ></textarea>
              </div>

              <!-- Start Date -->
              <div class="jcl-dash-form-group">
                <label for="jcl-job-start-date" class="jcl-dash-form-label">Start Date</label>
                <input
                  type="date"
                  id="jcl-job-start-date"
                  class="jcl-dash-form-input"
                >
              </div>

              <!-- Pay Rate -->
              <div class="jcl-dash-form-group">
                <label for="jcl-job-pay-rate" class="jcl-dash-form-label">Pay Rate (optional)</label>
                <input
                  type="text"
                  id="jcl-job-pay-rate"
                  class="jcl-dash-form-input"
                  placeholder="e.g., $45-55/hour, DOE"
                >
                <small class="jcl-dash-form-hint">Leaving this blank may increase applicant pool</small>
              </div>

              <div id="jcl-post-job-message" class="jcl-dash-message" style="display: none;"></div>

              <div class="jcl-dash-modal-actions">
                <button type="button" class="jcl-dash-btn jcl-dash-btn-secondary" onclick="JCLDashboard.closePostJob()">
                  Cancel
                </button>
                <button type="submit" class="jcl-dash-btn jcl-dash-btn-primary">
                  Post Job
                </button>
              </div>

            </form>

          </div>
        </div>
      `;

      // Insert modal into page
      document.body.insertAdjacentHTML('beforeend', modalHTML);

      // Add form submit handler
      document.getElementById('jcl-post-job-form').addEventListener('submit', (e) => {
        e.preventDefault();
        this.submitJobPosting();
      });

      // Add ESC key handler
      this.postJobEscHandler = (e) => {
        if (e.key === 'Escape') {
          this.closePostJob();
        }
      };
      document.addEventListener('keydown', this.postJobEscHandler);
    },

    /**
     * Submit job posting
     */
    async submitJobPosting() {
      const messageEl = document.getElementById('jcl-post-job-message');
      const submitBtn = document.querySelector('#jcl-post-job-form button[type="submit"]');

      // Get values from all fields
      const professionalTitle = document.getElementById('jcl-job-professional-title').value;
      const facilityName = document.getElementById('jcl-job-facility-name').value.trim();
      const ccnNpi = document.getElementById('jcl-job-ccn-npi').value.trim();
      const city = document.getElementById('jcl-job-city').value.trim();
      const state = document.getElementById('jcl-job-state').value;
      const hideFacility = document.getElementById('jcl-job-hide-facility').checked;
      const jobType = document.getElementById('jcl-job-type').value;
      const jobDuration = document.getElementById('jcl-job-duration').value;
      const jobStatus = document.getElementById('jcl-job-status').value;
      const emergentStatus = document.getElementById('jcl-job-emergent').value;
      const requisition = document.getElementById('jcl-job-requisition').value.trim();
      const specialty = document.getElementById('jcl-job-specialty').value.trim();
      const description = document.getElementById('jcl-job-description').value.trim();
      const startDate = document.getElementById('jcl-job-start-date').value;
      const payRate = document.getElementById('jcl-job-pay-rate').value.trim();

      // Validate required fields
      if (!professionalTitle || !facilityName || !ccnNpi || !city || !state ||
          !jobType || !jobDuration || !jobStatus || !emergentStatus || !description) {
        this.showMessage(messageEl, 'Please fill in all required fields', 'error');
        return;
      }

      // Show confirmation dialog
      const locationDisplay = hideFacility ? state : `${city}, ${state}`;
      const confirmMessage = `Are you sure you want to post this job?\n\n` +
        `Position: ${professionalTitle}\n` +
        `Location: ${locationDisplay}\n` +
        `Type: ${jobType}\n` +
        `Duration: ${jobDuration}\n\n` +
        `This will ${this.organization && this.organization.get('credits') > 0 ? 'use 1 credit' : 'require payment'}.`;

      if (!confirm(confirmMessage)) {
        return;
      }

      // Disable button
      submitBtn.disabled = true;
      submitBtn.textContent = 'Posting...';

      try {
        // Create job posting object
        const JobPosting = Parse.Object.extend('JobPosting');
        const jobPosting = new JobPosting();

        // Set all fields
        jobPosting.set('professionalTitle', professionalTitle);
        jobPosting.set('facilityName', facilityName);
        jobPosting.set('ccnNpi', ccnNpi);
        jobPosting.set('city', city);
        jobPosting.set('state', state);
        jobPosting.set('location', `${city}, ${state}`); // Combined location
        jobPosting.set('hideFacility', hideFacility); // NEW: Hide facility/city flag
        jobPosting.set('jobType', jobType);
        jobPosting.set('jobDuration', jobDuration);
        jobPosting.set('jobStatus', jobStatus);
        jobPosting.set('emergentStatus', emergentStatus);
        if (requisition) jobPosting.set('requisitionNumber', requisition);
        if (specialty) jobPosting.set('specialty', specialty);
        jobPosting.set('description', description);
        if (startDate) jobPosting.set('startDate', new Date(startDate));
        if (payRate) jobPosting.set('payRate', payRate);

        // Link to organization
        if (this.organization) {
          jobPosting.set('organization', this.organization);
        }

        // Link to current user
        jobPosting.set('postedBy', this.currentUser);

        // Set posting metadata
        jobPosting.set('status', 'active');
        jobPosting.set('postedAt', new Date());

        // Set ACL
        const acl = new Parse.ACL(this.currentUser);
        acl.setPublicReadAccess(true);
        jobPosting.setACL(acl);

        // Save to Parse
        await jobPosting.save();

        // Save city/state to organization for future jobs (if not already saved)
        if (this.organization) {
          const hasDefaultLocation = this.organization.get('defaultCity') && this.organization.get('defaultState');
          if (!hasDefaultLocation) {
            try {
              this.organization.set('defaultCity', city);
              this.organization.set('defaultState', state);
              await this.organization.save();
              console.log('[JCL Dashboard] Saved default location to organization');
            } catch (err) {
              console.warn('[JCL Dashboard] Could not save default location:', err);
              // Don't fail the job posting if this fails
            }
          }
        }

        this.showMessage(messageEl, 'Job posted successfully!', 'success');

        // Close modal after short delay
        setTimeout(() => {
          this.closePostJob();
          this.init(); // Reload dashboard to show updated stats
        }, 1500);

      } catch (error) {
        console.error('[JCL Dashboard] Post job error:', error);
        this.showMessage(messageEl, `Error: ${error.message}`, 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Post Job';
      }
    },

    /**
     * Close post job modal
     */
    closePostJob() {
      const modal = document.getElementById('jcl-post-job-modal');
      if (modal) {
        modal.remove();
      }
      if (this.postJobEscHandler) {
        document.removeEventListener('keydown', this.postJobEscHandler);
      }
    },

    /**
     * Get count of active job posts for the current user
     */
    async getJobPostsCount() {
      try {
        const JobPosting = Parse.Object.extend('JobPosting');
        const query = new Parse.Query(JobPosting);
        query.equalTo('postedBy', this.currentUser);
        query.equalTo('status', 'active');
        return await query.count();
      } catch (error) {
        console.error('[JCL Dashboard] Error counting jobs:', error);
        return 0;
      }
    },

    /**
     * Load and render user's posted jobs
     */
    async loadAndRenderJobs() {
      const jobsListEl = document.getElementById('jcl-dash-jobs-list');

      try {
        const JobPosting = Parse.Object.extend('JobPosting');
        const query = new Parse.Query(JobPosting);
        query.equalTo('postedBy', this.currentUser);
        query.notEqualTo('status', 'archived'); // Exclude archived jobs
        query.descending('createdAt');
        query.limit(50);

        const jobs = await query.find();

        if (jobs.length === 0) {
          jobsListEl.innerHTML = `
            <div class="jcl-dash-empty-state">
              <span class="jcl-dash-empty-icon">📋</span>
              <p>No jobs posted yet. Click "Post a Job" to get started!</p>
            </div>
          `;
          return;
        }

        let jobsHTML = '';
        jobs.forEach(job => {
          const title = job.get('professionalTitle') || 'Job';
          const facility = job.get('facilityName') || '';
          const location = job.get('location') || '';
          const status = job.get('jobStatus') || 'Active';
          const emergent = job.get('emergentStatus') === 'Yes';
          const postedDate = job.get('postedAt') || job.createdAt;
          const dateStr = postedDate ? postedDate.toLocaleDateString() : '';

          jobsHTML += `
            <div class="jcl-dash-job-card" onclick="JCLDashboard.editJob('${job.id}')">
              <div class="jcl-dash-job-header">
                <div>
                  <h3 class="jcl-dash-job-title">${this.escapeHtml(title)}</h3>
                  <p class="jcl-dash-job-facility">${this.escapeHtml(facility)} ${location ? '• ' + this.escapeHtml(location) : ''}</p>
                </div>
                <div class="jcl-dash-job-badges">
                  ${emergent ? '<span class="jcl-dash-job-badge jcl-badge-emergent">🚨 Emergent</span>' : ''}
                  <span class="jcl-dash-job-badge jcl-badge-${status.toLowerCase().replace(' ', '-')}">${this.escapeHtml(status)}</span>
                </div>
              </div>
              <div class="jcl-dash-job-meta">
                <span>📅 Posted: ${dateStr}</span>
              </div>
              <div class="jcl-dash-job-actions">
                <button
                  class="jcl-dash-job-action-btn jcl-dash-job-archive-btn"
                  onclick="event.stopPropagation(); JCLDashboard.archiveJob('${job.id}')"
                  title="Archive this job"
                >
                  📦
                </button>
                <button
                  class="jcl-dash-job-action-btn jcl-dash-job-delete-btn"
                  onclick="event.stopPropagation(); JCLDashboard.deleteJob('${job.id}')"
                  title="Delete this job"
                >
                  🗑️
                </button>
              </div>
            </div>
          `;
        });

        jobsListEl.innerHTML = jobsHTML;

      } catch (error) {
        console.error('[JCL Dashboard] Load jobs error:', error);
        jobsListEl.innerHTML = `
          <div class="jcl-dash-error">
            <p>Unable to load jobs</p>
            <p class="jcl-dash-error-detail">${error.message}</p>
          </div>
        `;
      }
    },

    /**
     * View profile modal
     */
    viewProfile() {
      const email = this.userProfile?.get('user')?.get('email') || this.currentUser.get('email') || '';
      const phone = this.userProfile?.get('phone') || this.currentUser.get('facContactPhone') || this.currentUser.get('phone') || '';
      const title = this.userProfile?.get('title') || '';
      const firstName = this.userProfile?.get('firstName') || '';
      const lastName = this.userProfile?.get('lastName') || '';
      const orgName = this.getOrgName();

      const modalHTML = `
        <div id="jcl-profile-modal" class="jcl-dash-modal">
          <div class="jcl-dash-modal-overlay" onclick="JCLDashboard.closeProfile()"></div>
          <div class="jcl-dash-modal-content">
            <button class="jcl-dash-modal-close" onclick="JCLDashboard.closeProfile()" aria-label="Close">&times;</button>
            <h2 class="jcl-dash-modal-title">Your Profile</h2>
            <div class="jcl-dash-profile-view">
              <div class="jcl-dash-info-row">
                <span class="jcl-dash-info-label">Name</span>
                <span class="jcl-dash-info-value">${this.escapeHtml(firstName)} ${this.escapeHtml(lastName)}</span>
              </div>
              <div class="jcl-dash-info-row">
                <span class="jcl-dash-info-label">Email</span>
                <span class="jcl-dash-info-value">${this.escapeHtml(email)}</span>
              </div>
              ${phone ? `
              <div class="jcl-dash-info-row">
                <span class="jcl-dash-info-label">Phone</span>
                <span class="jcl-dash-info-value">${this.escapeHtml(phone)}</span>
              </div>
              ` : ''}
              ${title ? `
              <div class="jcl-dash-info-row">
                <span class="jcl-dash-info-label">Title</span>
                <span class="jcl-dash-info-value">${this.escapeHtml(title)}</span>
              </div>
              ` : ''}
              <div class="jcl-dash-info-row">
                <span class="jcl-dash-info-label">Organization</span>
                <span class="jcl-dash-info-value">${this.escapeHtml(orgName)}</span>
              </div>
            </div>
            <div class="jcl-dash-modal-actions">
              <button class="jcl-dash-btn jcl-dash-btn-secondary" onclick="JCLDashboard.closeProfile()">Close</button>
              <button class="jcl-dash-btn jcl-dash-btn-primary" onclick="JCLDashboard.closeProfile(); JCLDashboard.openManageAccount();">Edit Profile</button>
            </div>
          </div>
        </div>
      `;

      document.body.insertAdjacentHTML('beforeend', modalHTML);
    },

    closeProfile() {
      const modal = document.getElementById('jcl-profile-modal');
      if (modal) modal.remove();
    },

    /**
     * View notifications modal
     */
    viewNotifications() {
      const verificationStatus = this.getVerificationStatus();
      const isVerified = verificationStatus === 'verified';

      const modalHTML = `
        <div id="jcl-notifications-modal" class="jcl-dash-modal">
          <div class="jcl-dash-modal-overlay" onclick="JCLDashboard.closeNotifications()"></div>
          <div class="jcl-dash-modal-content">
            <button class="jcl-dash-modal-close" onclick="JCLDashboard.closeNotifications()" aria-label="Close">&times;</button>
            <h2 class="jcl-dash-modal-title">Notifications</h2>
            <div class="jcl-dash-notifications-view">
              ${!isVerified ? `
              <div class="jcl-dash-alert jcl-dash-alert-warning">
                <span class="jcl-dash-alert-icon">⚠</span>
                <div>
                  <strong>Account Pending Verification</strong>
                  <p>Your account is pending verification. Posting limits may apply.</p>
                </div>
              </div>
              ` : `
              <div class="jcl-dash-empty-state">
                <span class="jcl-dash-empty-icon">📬</span>
                <p>No new notifications</p>
              </div>
              `}
            </div>
            <div class="jcl-dash-modal-actions">
              <button class="jcl-dash-btn jcl-dash-btn-primary" onclick="JCLDashboard.closeNotifications()">Close</button>
            </div>
          </div>
        </div>
      `;

      document.body.insertAdjacentHTML('beforeend', modalHTML);
    },

    closeNotifications() {
      const modal = document.getElementById('jcl-notifications-modal');
      if (modal) modal.remove();
    },

    /**
     * Edit job - open edit modal with job data
     */
    async editJob(jobId) {
      try {
        const JobPosting = Parse.Object.extend('JobPosting');
        const query = new Parse.Query(JobPosting);
        const job = await query.get(jobId);

        this.renderEditJobModal(job);
      } catch (error) {
        console.error('[JCL Dashboard] Edit job error:', error);
        alert(`Error loading job: ${error.message}`);
      }
    },

    /**
     * Render edit job modal
     */
    renderEditJobModal(job) {
      const usStates = ['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY'];

      const professionalTitle = job.get('professionalTitle') || '';
      const facilityName = job.get('facilityName') || '';
      const ccnNpi = job.get('ccnNpi') || '';
      const city = job.get('city') || '';
      const state = job.get('state') || '';
      const hideFacility = job.get('hideFacility') !== undefined ? job.get('hideFacility') : true; // Default to true if not set
      const jobType = job.get('jobType') || '';
      const jobDuration = job.get('jobDuration') || '';
      const jobStatus = job.get('jobStatus') || '';
      const emergentStatus = job.get('emergentStatus') || '';
      const requisition = job.get('requisitionNumber') || '';
      const specialty = job.get('specialty') || '';
      const description = job.get('description') || '';
      const startDate = job.get('startDate');
      const startDateStr = startDate ? startDate.toISOString().split('T')[0] : '';
      const payRate = job.get('payRate') || '';

      const modalHTML = `
        <div id="jcl-edit-job-modal" class="jcl-dash-modal">
          <div class="jcl-dash-modal-overlay" onclick="JCLDashboard.closeEditJob()"></div>
          <div class="jcl-dash-modal-content" style="max-width: 700px;">
            <button class="jcl-dash-modal-close" onclick="JCLDashboard.closeEditJob()" aria-label="Close">&times;</button>
            <h2 class="jcl-dash-modal-title">Edit Job Posting</h2>
            <p class="jcl-dash-modal-subtitle">Update the job details</p>
            <form id="jcl-edit-job-form" data-job-id="${job.id}">
              <div class="jcl-dash-form-group">
                <label for="jcl-edit-professional-title" class="jcl-dash-form-label">Professional Title *</label>
                <select id="jcl-edit-professional-title" class="jcl-dash-form-input" required>
                  <option value="">Select professional title</option>
                  ${['CRNA','CAA','Anesthesia','Registered Nurse(RN)','CNA','LPN/LVN','NP','CNS'].map(t =>
                    `<option value="${t}" ${t === professionalTitle ? 'selected' : ''}>${t.replace('Registered Nurse(RN)', 'Registered Nurse (RN)')}</option>`
                  ).join('')}
                </select>
              </div>
              <div class="jcl-dash-form-group">
                <label for="jcl-edit-facility-name" class="jcl-dash-form-label">Facility Name *</label>
                <input type="text" id="jcl-edit-facility-name" class="jcl-dash-form-input" value="${this.escapeHtml(facilityName)}" required>
              </div>
              <div class="jcl-dash-form-group">
                <label for="jcl-edit-ccn-npi" class="jcl-dash-form-label">CCN Number *</label>
                <input type="text" id="jcl-edit-ccn-npi" class="jcl-dash-form-input" value="${this.escapeHtml(ccnNpi)}" required>
              </div>
              <div class="jcl-dash-form-group">
                <label for="jcl-edit-city" class="jcl-dash-form-label">City *</label>
                <input type="text" id="jcl-edit-city" class="jcl-dash-form-input" value="${this.escapeHtml(city)}" required>
              </div>
              <div class="jcl-dash-form-group">
                <label for="jcl-edit-state" class="jcl-dash-form-label">State *</label>
                <select id="jcl-edit-state" class="jcl-dash-form-input" required>
                  <option value="">Select state</option>
                  ${usStates.map(s => `<option value="${s}" ${s === state ? 'selected' : ''}>${s}</option>`).join('')}
                </select>
              </div>

              <!-- Facility/City Visibility -->
              <div class="jcl-dash-form-group">
                <label class="jcl-dash-checkbox-container">
                  <input type="checkbox" id="jcl-edit-hide-facility" ${hideFacility ? 'checked' : ''}>
                  <span class="jcl-dash-checkbox-label">Hide facility name & city in job posting</span>
                </label>
                <small class="jcl-dash-form-hint">When checked, only the state will be visible to applicants. This prevents direct applications and protects your facility information.</small>
              </div>

              <div class="jcl-dash-form-group">
                <label for="jcl-edit-job-type" class="jcl-dash-form-label">Job Type *</label>
                <select id="jcl-edit-job-type" class="jcl-dash-form-input" required>
                  <option value="">Select job type</option>
                  ${['Locum','Permanent','Both'].map(t =>
                    `<option value="${t}" ${t === jobType ? 'selected' : ''}>${t}</option>`
                  ).join('')}
                </select>
              </div>
              <div class="jcl-dash-form-group">
                <label for="jcl-edit-job-duration" class="jcl-dash-form-label">Job Duration *</label>
                <select id="jcl-edit-job-duration" class="jcl-dash-form-input" required>
                  <option value="">Select duration</option>
                  ${['3 months','6 months','9 months','12 months','Flexible'].map(d =>
                    `<option value="${d}" ${d === jobDuration ? 'selected' : ''}>${d}</option>`
                  ).join('')}
                </select>
              </div>
              <div class="jcl-dash-form-group">
                <label for="jcl-edit-job-status" class="jcl-dash-form-label">Job Status *</label>
                <select id="jcl-edit-job-status" class="jcl-dash-form-input" required>
                  <option value="">Select status</option>
                  ${['Active','On Hold'].map(s =>
                    `<option value="${s}" ${s === jobStatus ? 'selected' : ''}>${s}</option>`
                  ).join('')}
                </select>
              </div>
              <div class="jcl-dash-form-group">
                <label for="jcl-edit-emergent" class="jcl-dash-form-label">Emergent Status *</label>
                <select id="jcl-edit-emergent" class="jcl-dash-form-input" required>
                  <option value="">Select emergent status</option>
                  ${['Yes','No'].map(e =>
                    `<option value="${e}" ${e === emergentStatus ? 'selected' : ''}>${e}</option>`
                  ).join('')}
                </select>
              </div>
              <div class="jcl-dash-form-group">
                <label for="jcl-edit-requisition" class="jcl-dash-form-label">Requisition Number</label>
                <input type="text" id="jcl-edit-requisition" class="jcl-dash-form-input" value="${this.escapeHtml(requisition)}">
              </div>
              <div class="jcl-dash-form-group">
                <label for="jcl-edit-specialty" class="jcl-dash-form-label">Specialty</label>
                <input type="text" id="jcl-edit-specialty" class="jcl-dash-form-input" value="${this.escapeHtml(specialty)}">
              </div>
              <div class="jcl-dash-form-group">
                <label for="jcl-edit-description" class="jcl-dash-form-label">Job Description *</label>
                <textarea id="jcl-edit-description" class="jcl-dash-form-input" rows="4" required style="resize: vertical; font-family: inherit;">${this.escapeHtml(description)}</textarea>
              </div>
              <div class="jcl-dash-form-group">
                <label for="jcl-edit-start-date" class="jcl-dash-form-label">Start Date</label>
                <input type="date" id="jcl-edit-start-date" class="jcl-dash-form-input" value="${startDateStr}">
              </div>
              <div class="jcl-dash-form-group">
                <label for="jcl-edit-pay-rate" class="jcl-dash-form-label">Pay Rate (optional)</label>
                <input type="text" id="jcl-edit-pay-rate" class="jcl-dash-form-input" value="${this.escapeHtml(payRate)}">
              </div>
              <div id="jcl-edit-job-message" class="jcl-dash-message" style="display: none;"></div>
              <div class="jcl-dash-modal-actions">
                <button type="button" class="jcl-dash-btn jcl-dash-btn-secondary" onclick="JCLDashboard.closeEditJob()">Cancel</button>
                <button type="submit" class="jcl-dash-btn jcl-dash-btn-primary">Update Job</button>
              </div>
            </form>
          </div>
        </div>
      `;

      document.body.insertAdjacentHTML('beforeend', modalHTML);

      document.getElementById('jcl-edit-job-form').addEventListener('submit', (e) => {
        e.preventDefault();
        this.updateJob(job.id);
      });
    },

    /**
     * Update job
     */
    async updateJob(jobId) {
      const messageEl = document.getElementById('jcl-edit-job-message');
      const submitBtn = document.querySelector('#jcl-edit-job-form button[type="submit"]');

      const professionalTitle = document.getElementById('jcl-edit-professional-title').value;
      const facilityName = document.getElementById('jcl-edit-facility-name').value.trim();
      const ccnNpi = document.getElementById('jcl-edit-ccn-npi').value.trim();
      const city = document.getElementById('jcl-edit-city').value.trim();
      const state = document.getElementById('jcl-edit-state').value;
      const jobType = document.getElementById('jcl-edit-job-type').value;
      const jobDuration = document.getElementById('jcl-edit-job-duration').value;
      const jobStatus = document.getElementById('jcl-edit-job-status').value;
      const emergentStatus = document.getElementById('jcl-edit-emergent').value;
      const requisition = document.getElementById('jcl-edit-requisition').value.trim();
      const specialty = document.getElementById('jcl-edit-specialty').value.trim();
      const description = document.getElementById('jcl-edit-description').value.trim();
      const startDate = document.getElementById('jcl-edit-start-date').value;
      const payRate = document.getElementById('jcl-edit-pay-rate').value.trim();
      const hideFacility = document.getElementById('jcl-edit-hide-facility').checked;

      if (!professionalTitle || !facilityName || !ccnNpi || !city || !state ||
          !jobType || !jobDuration || !jobStatus || !emergentStatus || !description) {
        this.showMessage(messageEl, 'Please fill in all required fields', 'error');
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = 'Updating...';

      try {
        const JobPosting = Parse.Object.extend('JobPosting');
        const query = new Parse.Query(JobPosting);
        const job = await query.get(jobId);

        job.set('professionalTitle', professionalTitle);
        job.set('facilityName', facilityName);
        job.set('ccnNpi', ccnNpi);
        job.set('city', city);
        job.set('state', state);
        job.set('location', `${city}, ${state}`);
        job.set('jobType', jobType);
        job.set('jobDuration', jobDuration);
        job.set('jobStatus', jobStatus);
        job.set('emergentStatus', emergentStatus);
        if (requisition) job.set('requisitionNumber', requisition);
        if (specialty) job.set('specialty', specialty);
        job.set('description', description);
        if (startDate) job.set('startDate', new Date(startDate));
        if (payRate) job.set('payRate', payRate);
        job.set('hideFacility', hideFacility);

        await job.save();

        this.showMessage(messageEl, 'Job updated successfully!', 'success');

        setTimeout(() => {
          this.closeEditJob();
          this.loadAndRenderJobs(); // Reload jobs list
        }, 1500);

      } catch (error) {
        console.error('[JCL Dashboard] Update job error:', error);
        this.showMessage(messageEl, `Error: ${error.message}`, 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Update Job';
      }
    },

    closeEditJob() {
      const modal = document.getElementById('jcl-edit-job-modal');
      if (modal) modal.remove();
    },

    /**
     * Logout
     */
    async logout() {
      if (!confirm('Are you sure you want to log out?')) {
        return;
      }

      try {
        await Parse.User.logOut();
        window.location.href = '/'; // Redirect to homepage
      } catch (error) {
        console.error('[JCL Dashboard] Logout error:', error);
        alert('Error logging out. Please try again.');
      }
    },

    /**
     * Initialize meteor/shooting star animation
     */
    initializeMeteorAnimation() {
      // Check if canvas already exists
      if (document.getElementById('meteor-canvas')) {
        return;
      }

      // Create and add canvas to body
      const canvas = document.createElement('canvas');
      canvas.id = 'meteor-canvas';
      canvas.style.cssText = 'position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; z-index: 0; pointer-events: none;';
      document.body.insertBefore(canvas, document.body.firstChild);

      // Meteor animation configuration
      const CONFIG = {
        meteorFrequencyRangeSeconds: [2, 6],
        maxMeteorsOnScreen: 3,
        meteorSpeedRange: [200, 400],
        trailLengthRange: [80, 150],
        brightnessRange: [0.6, 1.0],
        parallaxStrength: 3,
        starCount: 80,
        starTwinkleSpeed: 0.0005
      };

      const ctx = canvas.getContext('2d');
      let width = canvas.width = window.innerWidth;
      let height = canvas.height = window.innerHeight;

      let meteors = [];
      let stars = [];
      let lastMeteorTime = 0;
      let parallaxX = 0;
      let parallaxY = 0;
      let lastFrameTime = performance.now();

      // Initialize Stars
      function initStars() {
        stars = [];
        for (let i = 0; i < CONFIG.starCount; i++) {
          stars.push({
            x: Math.random() * width,
            y: Math.random() * height,
            size: Math.random() * 1.5 + 0.5,
            opacity: Math.random() * 0.5 + 0.3,
            twinkleSpeed: Math.random() * CONFIG.starTwinkleSpeed + CONFIG.starTwinkleSpeed,
            twinklePhase: Math.random() * Math.PI * 2
          });
        }
      }

      // Create Meteor
      function createMeteor() {
        const angle = Math.random() * Math.PI / 3 + Math.PI / 6;
        const speed = Math.random() * (CONFIG.meteorSpeedRange[1] - CONFIG.meteorSpeedRange[0]) + CONFIG.meteorSpeedRange[0];

        meteors.push({
          x: Math.random() * width * 0.5,
          y: Math.random() * height * 0.3,
          vx: Math.cos(angle) * speed,
          vy: Math.sin(angle) * speed,
          life: 1.0,
          decay: 0.008 + Math.random() * 0.004,
          trailLength: Math.random() * (CONFIG.trailLengthRange[1] - CONFIG.trailLengthRange[0]) + CONFIG.trailLengthRange[0],
          brightness: Math.random() * (CONFIG.brightnessRange[1] - CONFIG.brightnessRange[0]) + CONFIG.brightnessRange[0]
        });
      }

      // Draw Stars
      function drawStars(time) {
        ctx.save();
        ctx.translate(parallaxX * 0.5, parallaxY * 0.5);

        stars.forEach(star => {
          const twinkle = Math.sin(time * star.twinkleSpeed + star.twinklePhase) * 0.3 + 0.7;
          const opacity = star.opacity * twinkle;

          ctx.fillStyle = `rgba(255, 255, 255, ${opacity})`;
          ctx.beginPath();
          ctx.arc(star.x, star.y, star.size, 0, Math.PI * 2);
          ctx.fill();
        });

        ctx.restore();
      }

      // Draw Meteors
      function drawMeteors(deltaTime) {
        ctx.save();
        ctx.globalCompositeOperation = 'lighter';
        ctx.translate(parallaxX, parallaxY);

        meteors = meteors.filter(meteor => {
          meteor.x += meteor.vx * deltaTime / 1000;
          meteor.y += meteor.vy * deltaTime / 1000;
          meteor.life -= meteor.decay;

          if (meteor.life <= 0 || meteor.x > width + 100 || meteor.y > height + 100) {
            return false;
          }

          const tailX = meteor.x - (meteor.vx / Math.abs(meteor.vx + meteor.vy)) * meteor.trailLength;
          const tailY = meteor.y - (meteor.vy / Math.abs(meteor.vx + meteor.vy)) * meteor.trailLength;

          const gradient = ctx.createLinearGradient(meteor.x, meteor.y, tailX, tailY);
          gradient.addColorStop(0, `rgba(255, 255, 255, ${meteor.life * meteor.brightness})`);
          gradient.addColorStop(0.5, `rgba(173, 216, 230, ${meteor.life * meteor.brightness * 0.5})`);
          gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');

          ctx.strokeStyle = gradient;
          ctx.lineWidth = 2 + meteor.life;
          ctx.beginPath();
          ctx.moveTo(meteor.x, meteor.y);
          ctx.lineTo(tailX, tailY);
          ctx.stroke();

          ctx.fillStyle = `rgba(255, 255, 255, ${meteor.life})`;
          ctx.beginPath();
          ctx.arc(meteor.x, meteor.y, 2, 0, Math.PI * 2);
          ctx.fill();

          return true;
        });

        ctx.restore();
      }

      // Animation Loop
      function animate(currentTime) {
        const deltaTime = currentTime - lastFrameTime;
        lastFrameTime = currentTime;

        ctx.clearRect(0, 0, width, height);

        drawStars(currentTime);
        drawMeteors(deltaTime);

        // Create new meteors
        const minInterval = CONFIG.meteorFrequencyRangeSeconds[0] * 1000;
        const maxInterval = CONFIG.meteorFrequencyRangeSeconds[1] * 1000;
        const nextMeteorTime = lastMeteorTime + Math.random() * (maxInterval - minInterval) + minInterval;

        if (currentTime >= nextMeteorTime && meteors.length < CONFIG.maxMeteorsOnScreen) {
          createMeteor();
          lastMeteorTime = currentTime;
        }

        requestAnimationFrame(animate);
      }

      // Mouse/Touch Parallax
      function updateParallax(e) {
        const x = e.clientX !== undefined ? e.clientX : (e.touches ? e.touches[0].clientX : width / 2);
        const y = e.clientY !== undefined ? e.clientY : (e.touches ? e.touches[0].clientY : height / 2);

        parallaxX = ((x / width) - 0.5) * CONFIG.parallaxStrength;
        parallaxY = ((y / height) - 0.5) * CONFIG.parallaxStrength;
      }

      // Window Resize
      function handleResize() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
        initStars();
      }

      // Event Listeners
      window.addEventListener('mousemove', updateParallax);
      window.addEventListener('touchmove', updateParallax);
      window.addEventListener('resize', handleResize);

      // Initialize
      initStars();
      requestAnimationFrame(animate);

      console.log('[JCL Dashboard] Meteor animation initialized');
    },

    /**
     * Enable a locked field for editing
     */
    enableField(fieldId) {
      const field = document.getElementById(fieldId);
      if (!field) return;

      if (field.tagName === 'SELECT') {
        field.disabled = false;
        field.style.backgroundColor = '';
        field.style.color = '';
      } else {
        field.readOnly = false;
        field.style.backgroundColor = '';
        field.style.color = '';
      }

      // Remove the edit button
      const label = field.previousElementSibling;
      const editBtn = label?.querySelector('.jcl-edit-field-btn');
      if (editBtn) editBtn.remove();

      // Focus the field
      field.focus();
    },

    /**
     * Show purchase credits modal when user has no credits
     */
    showPurchaseCreditsModal() {
      const costPerPost = this.costPerPost || 1;

      // Calculate how many posts each package provides
      const packages = [
        { credits: 10, price: 29.99 },
        { credits: 25, price: 59.99, popular: true },
        { credits: 50, price: 99.99 },
        { credits: 100, price: 149.99 }
      ];

      const packagesHTML = packages.map((pkg, index) => {
        const pricePerCredit = (pkg.price / pkg.credits).toFixed(2);
        const jobPosts = Math.floor(pkg.credits / costPerPost);
        const savings = index === 0 ? 0 : Math.round((1 - (pkg.price / pkg.credits) / (packages[0].price / packages[0].credits)) * 100);

        return `
          <div class="jcl-dash-credit-package ${pkg.popular ? 'jcl-dash-package-popular' : ''}" onclick="JCLDashboard.selectCreditPackage(${pkg.credits}, ${pkg.price})">
            ${pkg.popular ? '<div class="jcl-dash-package-badge">POPULAR</div>' : ''}
            <div class="jcl-dash-package-credits">${pkg.credits} Credits</div>
            <div class="jcl-dash-package-price">$${pkg.price.toFixed(2)}</div>
            <div class="jcl-dash-package-per-credit">$${pricePerCredit} per credit</div>
            <div class="jcl-dash-package-posts">${jobPosts} Job ${jobPosts === 1 ? 'Post' : 'Posts'}</div>
            ${savings > 0 ? `<div class="jcl-dash-package-savings">Save ${savings}%</div>` : ''}
          </div>
        `;
      }).join('');

      const modalHTML = `
        <div id="purchaseCreditsModal" class="jcl-dash-modal">
          <div class="jcl-dash-modal-overlay" onclick="JCLDashboard.closePurchaseCreditsModal()"></div>
          <div class="jcl-dash-modal-content" style="max-width: 900px; max-height: 90vh; overflow-y: auto;">
            <button class="jcl-dash-modal-close" onclick="JCLDashboard.closePurchaseCreditsModal()" aria-label="Close">&times;</button>

            <h2 class="jcl-dash-modal-title">Purchase Job Posting Credits</h2>
            <p class="jcl-dash-modal-subtitle">Select a credit package to continue posting jobs</p>

            <p style="margin-bottom: 10px; color: #666;">
              You've used all your available credits. Purchase more to continue posting jobs.
            </p>
            <p style="margin-bottom: 20px; color: #EE6C4D; font-weight: 600;">
              Current rate: ${costPerPost} ${costPerPost === 1 ? 'credit' : 'credits'} per job post
            </p>

            <div class="jcl-dash-credit-packages">
              ${packagesHTML}
            </div>

            <div style="margin-top: 20px; padding: 15px; background: #f0f9ff; border-radius: 5px; border-left: 4px solid #3b82f6;">
              <strong>💡 Note:</strong> Credits never expire and can be used anytime to post jobs.
            </div>
          </div>
        </div>
      `;

      document.body.insertAdjacentHTML('beforeend', modalHTML);

      // Add ESC key handler
      const escHandler = (e) => {
        if (e.key === 'Escape') {
          this.closePurchaseCreditsModal();
          document.removeEventListener('keydown', escHandler);
        }
      };
      document.addEventListener('keydown', escHandler);
    },

    /**
     * Close purchase credits modal
     */
    closePurchaseCreditsModal() {
      const modal = document.getElementById('purchaseCreditsModal');
      if (modal) {
        modal.remove();
      }
    },

    /**
     * Handle credit package selection and initiate payment
     */
    async selectCreditPackage(credits, price) {
      try {
        // Close the modal
        this.closePurchaseCreditsModal();

        // Show loading state
        this.showNotification('Processing payment...', 'info');

        // TODO: Integrate with payment processor (Stripe, PayPal, etc.)
        // For now, we'll simulate the payment process

        // Example Stripe integration would look like:
        /*
        const stripe = Stripe('your_publishable_key');
        const response = await fetch('/create-checkout-session', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ credits, price })
        });
        const session = await response.json();
        await stripe.redirectToCheckout({ sessionId: session.id });
        */

        // Temporary: Show payment options
        const proceed = confirm(
          `Purchase ${credits} credits for $${price.toFixed(2)}?\n\n` +
          `This will open a payment page. After successful payment, your credits will be added automatically.`
        );

        if (proceed) {
          // Open payment page (replace with actual payment URL)
          // window.location.href = `/payment?credits=${credits}&price=${price}`;

          // For demo purposes, show success message
          this.showNotification(
            'Payment integration required. Please contact support to purchase credits.',
            'info'
          );
        }

      } catch (error) {
        console.error('Error processing credit purchase:', error);
        this.showNotification('Error processing payment. Please try again.', 'error');
      }
    },

    /**
     * Delete job posting
     */
    async deleteJob(jobId) {
      // Show confirmation dialog
      const confirmDelete = confirm(
        'Are you sure you want to delete this job posting?\n\n' +
        'This action cannot be undone. The job will be permanently removed.'
      );

      if (!confirmDelete) {
        return;
      }

      try {
        const JobPosting = Parse.Object.extend('JobPosting');
        const query = new Parse.Query(JobPosting);
        const job = await query.get(jobId);

        // Delete the job
        await job.destroy();

        this.showNotification('Job deleted successfully', 'success');

        // Reload jobs list
        this.loadJobs();

      } catch (error) {
        console.error('[JCL Dashboard] Delete job error:', error);
        this.showNotification('Error deleting job: ' + error.message, 'error');
      }
    },

    /**
     * Archive job posting
     */
    async archiveJob(jobId) {
      // Show confirmation dialog
      const confirmArchive = confirm(
        'Archive this job posting?\n\n' +
        'The job will be moved to your archives and hidden from active listings. ' +
        'You can view archived jobs from your dashboard settings.'
      );

      if (!confirmArchive) {
        return;
      }

      try {
        const JobPosting = Parse.Object.extend('JobPosting');
        const query = new Parse.Query(JobPosting);
        const job = await query.get(jobId);

        // Set job status to archived
        job.set('status', 'archived');
        job.set('archivedAt', new Date());
        await job.save();

        this.showNotification('Job archived successfully', 'success');

        // Reload jobs list
        this.loadJobs();

      } catch (error) {
        console.error('[JCL Dashboard] Archive job error:', error);
        this.showNotification('Error archiving job: ' + error.message, 'error');
      }
    },

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

  };

  // Auto-initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => JCLDashboard.init());
  } else {
    JCLDashboard.init();
  }

})();

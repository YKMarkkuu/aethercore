<div id="settingsModal" class="settings-modal hidden">
    <div class="settings-modal-content">
        <div class="settings-modal-header">
            <h2>⚙️ Settings</h2>
            <button class="settings-modal-close" onclick="closeSettings()">✕</button>
        </div>
        
        <div class="settings-modal-body">
            <!-- ===== LEFT SIDEBAR ===== -->
            <div class="settings-nav">
                <!-- Account -->
                <div class="settings-nav-category">Account</div>
                <button class="settings-nav-item active" onclick="switchSettingsTab('account')">Account Info</button>
                <button class="settings-nav-item" onclick="switchSettingsTab('security')">Password & Security</button>
                <button class="settings-nav-item" onclick="switchSettingsTab('devices')">Logged-in Devices</button>
                
                <!-- Profile -->
                <div class="settings-nav-category">Profile</div>
                <button class="settings-nav-item" onclick="switchSettingsTab('profile')">Profile Info</button>
                <button class="settings-nav-item" onclick="switchSettingsTab('avatar')">Avatar & Banner</button>
                
                <!-- Privacy -->
                <div class="settings-nav-category">Privacy</div>
                <button class="settings-nav-item" onclick="switchSettingsTab('privacy')">Privacy Settings</button>
                <button class="settings-nav-item" onclick="switchSettingsTab('friends')">Friend Requests</button>
                
                <!-- Appearance -->
                <div class="settings-nav-category">Appearance</div>
                <button class="settings-nav-item" onclick="switchSettingsTab('appearance')">Theme & Colors</button>
                
                <!-- Notifications -->
                <div class="settings-nav-category">Notifications</div>
                <button class="settings-nav-item" onclick="switchSettingsTab('notifications')">Notification Settings</button>
                
                <!-- Data -->
                <div class="settings-nav-category">Data</div>
                <button class="settings-nav-item" onclick="switchSettingsTab('data')">Data Management</button>
            </div>
            
            <!-- ===== RIGHT CONTENT ===== -->
            <div class="settings-content">
                <!-- Account Info -->
                <div id="settings-account" class="settings-tab">
                    <h3>Account Info</h3>
                    <p class="settings-subtitle">View and manage your account details</p>
                    
                    <div class="settings-group">
                        <label>Username</label>
                        <input type="text" value="{{ Auth::user()->name }}" class="settings-input">
                        <span class="settings-hint">Your unique username</span>
                    </div>
                    
                    <div class="settings-group">
                        <label>Email Address</label>
                        <input type="email" value="{{ Auth::user()->email }}" class="settings-input">
                        <span class="settings-hint">Used for login and notifications</span>
                    </div>
                    
                    <div class="settings-group">
                        <label>Display Name</label>
                        <input type="text" value="{{ Auth::user()->profile->display_name ?? Auth::user()->name }}" class="settings-input">
                        <span class="settings-hint">What others see on your profile</span>
                    </div>
                </div>
                
                <!-- Password & Security -->
                <div id="settings-security" class="settings-tab hidden">
                    <h3>Password & Security</h3>
                    <p class="settings-subtitle">Keep your account secure</p>
                    
                    <div class="settings-group">
                        <label>Current Password</label>
                        <input type="password" placeholder="••••••••" class="settings-input">
                    </div>
                    
                    <div class="settings-group">
                        <label>New Password</label>
                        <input type="password" placeholder="••••••••" class="settings-input">
                    </div>
                    
                    <div class="settings-group">
                        <label>Confirm New Password</label>
                        <input type="password" placeholder="••••••••" class="settings-input">
                    </div>
                    
                    <button class="settings-btn" onclick="alert('Password changed successfully!')">Change Password</button>
                </div>
                
                <!-- Logged-in Devices -->
                <div id="settings-devices" class="settings-tab hidden">
                    <h3>Logged-in Devices</h3>
                    <p class="settings-subtitle">Manage where you're logged in</p>
                    
                    <div class="device-item">
                        <div>
                            <div class="device-name">Current Device</div>
                            <div class="device-detail">Windows • Chrome • 127.0.0.1</div>
                        </div>
                        <span class="device-status">Active Now</span>
                    </div>
                    
                    <div class="device-item">
                        <div>
                            <div class="device-name">Phone</div>
                            <div class="device-detail">iPhone 15 • Safari • 2 hours ago</div>
                        </div>
                        <button class="device-logout-btn">Log Out</button>
                    </div>
                </div>
                
                <!-- Profile Info -->
                <div id="settings-profile" class="settings-tab hidden">
                    <h3>Profile Info</h3>
                    <p class="settings-subtitle">Tell the community about yourself</p>
                    
                    <div class="settings-group">
                        <label>Bio</label>
                        <textarea class="settings-input" rows="3" placeholder="Write something about yourself...">{{ Auth::user()->profile->bio ?? '' }}</textarea>
                        <span class="settings-hint">A short description about you</span>
                    </div>
                    
                    <div class="settings-group">
                        <label>Location</label>
                        <input type="text" placeholder="City, Country" class="settings-input">
                    </div>
                    
                    <button class="settings-btn" onclick="alert('Profile updated successfully!')">Save Profile</button>
                </div>
                
                <!-- Avatar & Banner -->
                <div id="settings-avatar" class="settings-tab hidden">
                    <h3>Avatar & Banner</h3>
                    <p class="settings-subtitle">Customize your profile appearance</p>
                    
                    <div style="display: flex; gap: 1.5rem; align-items: center; margin-bottom: 1rem;">
                        <div style="width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #3a7bd5, #1a4a9e); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 700; color: white; flex-shrink: 0; border: 2px solid rgba(255,255,255,0.3);">
                            {{ Auth::user()->name[0] }}
                        </div>
                        <div>
                            <button class="settings-btn" onclick="alert('Avatar upload coming soon!')">Upload Avatar</button>
                            <div style="font-size: 0.65rem; color: #6a6a6a; margin-top: 0.2rem;">PNG, JPG or GIF up to 5MB</div>
                        </div>
                    </div>
                    
                    <div class="settings-group">
                        <label>Banner Image</label>
                        <button class="settings-btn" onclick="alert('Banner upload coming soon!')">Upload Banner</button>
                        <span class="settings-hint">Recommended size: 1200 x 300px</span>
                    </div>
                </div>
                
                <!-- Privacy Settings -->
                <div id="settings-privacy" class="settings-tab hidden">
                    <h3>Privacy Settings</h3>
                    <p class="settings-subtitle">Control who can see your activity</p>
                    
                    <div class="settings-group">
                        <label>Online Status</label>
                        <select class="settings-input">
                            <option>Online</option>
                            <option>Idle</option>
                            <option>Do Not Disturb</option>
                            <option>Invisible</option>
                        </select>
                    </div>
                    
                    <div class="settings-group">
                        <label>Profile Visibility</label>
                        <select class="settings-input">
                            <option>Public</option>
                            <option>Friends Only</option>
                            <option>Private</option>
                        </select>
                    </div>
                </div>
                
                <!-- Friend Requests -->
                <div id="settings-friends" class="settings-tab hidden">
                    <h3>Friend Requests</h3>
                    <p class="settings-subtitle">Manage how you receive friend requests</p>
                    
                    <div class="settings-group">
                        <label>Who can send you friend requests?</label>
                        <select class="settings-input">
                            <option>Everyone</option>
                            <option>Friends of Friends</option>
                            <option>No One</option>
                        </select>
                    </div>
                    
                    <div class="settings-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" checked> Notify me when someone sends a friend request
                        </label>
                    </div>
                </div>
                
                <!-- Theme & Colors -->
                <div id="settings-appearance" class="settings-tab hidden">
                    <h3>Theme & Colors</h3>
                    <p class="settings-subtitle">Customize your AetherCore experience</p>
                    
                    <div class="settings-group">
                        <label>Theme</label>
                        <select class="settings-input">
                            <option>Windows XP</option>
                            <option>Dark Mode</option>
                            <option>Light Mode</option>
                        </select>
                    </div>
                    
                    <div class="settings-group">
                        <label>Accent Color</label>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <button class="color-option active" style="background: #3a7bd5;"></button>
                            <button class="color-option" style="background: #5cb85c;"></button>
                            <button class="color-option" style="background: #d9534f;"></button>
                            <button class="color-option" style="background: #f0ad4e;"></button>
                            <button class="color-option" style="background: #9460b8;"></button>
                        </div>
                    </div>
                </div>
                
                <!-- Notification Settings -->
                <div id="settings-notifications" class="settings-tab hidden">
                    <h3>Notification Settings</h3>
                    <p class="settings-subtitle">Choose what notifications you receive</p>
                    
                    <div class="settings-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" checked> Direct Messages
                        </label>
                    </div>
                    
                    <div class="settings-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" checked> Friend Requests
                        </label>
                    </div>
                    
                    <div class="settings-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox"> Space Invites
                        </label>
                    </div>
                    
                    <div class="settings-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" checked> Mentions & Replies
                        </label>
                    </div>
                </div>
                
                <!-- Data Management -->
                <div id="settings-data" class="settings-tab hidden">
                    <h3>Data Management</h3>
                    <p class="settings-subtitle">Control your data and account</p>
                    
                    <div style="border: 1px solid #d0c8c0; border-radius: 4px; padding: 1rem; margin-bottom: 0.75rem; background: #f8f5ec;">
                        <h4 style="font-size: 0.8rem; margin-bottom: 0.3rem;">Export Your Data</h4>
                        <p style="font-size: 0.7rem; color: #6a6a6a; margin-bottom: 0.5rem;">Download all your AetherCore data</p>
                        <button class="settings-btn" onclick="alert('Data export started! Check your email.')">Export Data</button>
                    </div>
                    
                    <div style="border: 1px solid #d0c8c0; border-radius: 4px; padding: 1rem; background: #f8f5ec; border-color: #d0a0a0;">
                        <h4 style="font-size: 0.8rem; color: #8a4040; margin-bottom: 0.3rem;">Delete Account</h4>
                        <p style="font-size: 0.7rem; color: #6a6a6a; margin-bottom: 0.5rem;">This action cannot be undone</p>
                        <button class="settings-btn settings-btn-danger" onclick="if(confirm('Are you sure you want to delete your account?')) alert('Account deletion requested.')">Delete Account</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
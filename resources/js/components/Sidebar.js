import React from 'react';

const Sidebar = () => {
  const menuItems = [
    { id: 1, title: 'Dashboard', icon: 'home', link: '/dashboard' },
    { id: 2, title: 'Jobs', icon: 'briefcase', link: '/jobs' },
    { id: 3, title: 'Applications', icon: 'file-text', link: '/applications' },
    { id: 4, title: 'Analytics', icon: 'trending-up', link: '/analytics' },
    { id: 5, title: 'Company Profile', icon: 'building', link: '/company-profile' },
  ];

  const settingsItems = [
    { id: 6, title: 'General', icon: 'settings', link: '/settings/general' },
    { id: 7, title: 'Notifications', icon: 'bell', link: '/settings/notifications' },
    { id: 8, title: 'Security', icon: 'shield', link: '/settings/security' },
    { id: 9, title: 'Logout', icon: 'log-out', link: '/logout' },
  ];

  return (
    <div className="w-64 min-h-screen bg-white shadow-lg">
      {/* User Profile */}
      <div className="p-4 border-b">
        <div className="flex items-center space-x-3">
          <div className="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white text-xl">
            a
          </div>
          <div>
            <h3 className="font-medium text-gray-800">antonio</h3>
            <p className="text-sm text-gray-500">antonio@gmail.com</p>
          </div>
        </div>
      </div>

      {/* Platform Section */}
      <div className="p-4">
        <h2 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
          PLATFORM
        </h2>
        <nav className="space-y-1">
          {menuItems.map((item) => (
            <a
              key={item.id}
              href={item.link}
              className="flex items-center px-3 py-2 text-gray-700 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
            >
              <i className={`feather-${item.icon} w-5 h-5 mr-3`}></i>
              <span>{item.title}</span>
            </a>
          ))}
        </nav>
      </div>

      {/* Settings Section */}
      <div className="p-4">
        <h2 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
          SETTINGS
        </h2>
        <nav className="space-y-1">
          {settingsItems.map((item) => (
            <a
              key={item.id}
              href={item.link}
              className="flex items-center px-3 py-2 text-gray-700 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
            >
              <i className={`feather-${item.icon} w-5 h-5 mr-3`}></i>
              <span>{item.title}</span>
            </a>
          ))}
        </nav>
      </div>
    </div>
  );
};

export default Sidebar; 
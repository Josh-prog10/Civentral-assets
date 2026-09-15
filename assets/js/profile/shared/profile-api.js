// Shared Profile API
// Responsible for any API requests shared between multiple profile sub-modules

async function civentralFetchProfile(endpoint = '../../api/employee/profile.php') {
  try {
    const response = await fetch(endpoint);
    const result = await response.json();
    return result;
  } catch (err) {
    console.error('Shared Profile API Error:', err);
    return { status: 'error', message: 'Network error fetching profile data.' };
  }
}

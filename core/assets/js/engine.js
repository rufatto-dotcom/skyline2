async function runBehaviorEngine() {

  if (!window.behavior) return;

  const behaviorName =
    typeof window.behavior === 'string'
      ? window.behavior
      : window.behavior.name;

  if (!behaviorName) return;

  const behaviorPath = `./behaviors/${behaviorName}.js`;

  try {
    await import(behaviorPath);
  } catch (error) {
    console.error(error);
    throw new Error(`Behavior JS '${behaviorName}' não encontrado em ${behaviorPath}`);
  }
}

runBehaviorEngine();

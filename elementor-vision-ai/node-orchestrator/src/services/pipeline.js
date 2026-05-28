import { visionAnalyzer, patternMatcher, jsonEngineer, responsiveEngineer, validatorAgent, visualDiffAgent } from '../agents/index.js';
export async function runPipeline({image_url,options={}}){
  let analysis=await visionAnalyzer({image_url});
  let similarity=0, iterations=[];
  for(let i=0;i<(options.max_iterations||3);i++){
    const matched=await patternMatcher(analysis);
    const draft=await jsonEngineer(matched);
    const responsive=await responsiveEngineer(draft);
    const validated=await validatorAgent(responsive);
    const diff=await visualDiffAgent({image_url,json:validated.json});
    similarity=diff.similarity; iterations.push({iteration:i+1,similarity,corrections:diff.corrections});
    analysis={...analysis, corrections: diff.corrections};
    if(similarity >= (options.target_similarity||0.92)) break;
  }
  return {analysis, similarity, iterations};
}

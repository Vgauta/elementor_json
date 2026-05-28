import express from 'express';
import { runPipeline } from './services/pipeline.js';
const app=express(); app.use(express.json({limit:'10mb'}));
app.post('/pipeline/run', async (req,res)=>{ const out=await runPipeline(req.body); res.json(out);});
app.listen(8787,()=>console.log('EVAI orchestrator on 8787'));
